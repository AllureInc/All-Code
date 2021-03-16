<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $_walletcreditAmountFactory;
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $_walletTransactionFactory;
    /**
     * @var WalletrecordFactory
     */
    protected $_wallerRecordFactory;
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $_salesOrderFactory;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date
     * @param \Webkul\Walletsystem\Helper\Data                     $helper
     * @param \Webkul\Walletsystem\Helper\Mail                     $mailHelper
     * @param \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param WallettransactionFactory                             $walletTransaction
     * @param WalletrecordFactory                                  $walletRecord
     * @param OrderFactory                                         $orderFactory
     * @param WalletUpdateData                                     $walletUpdateData
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecord,
        OrderFactory $orderFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->_date = $date;
        $this->_helper = $helper;
        $this->_mailHelper = $mailHelper;
        $this->_walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->_walletTransactionFactory = $walletTransaction;
        $this->_wallerRecordFactory = $walletRecord;
        $this->_salesOrderFactory = $orderFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * sales order save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $orderId = $observer->getOrder()->getId();
        $order = $observer->getOrder();
        if ($order->getStatus() == 'complete') {
            $incrementId = $this->_salesOrderFactory
                ->create()
                ->load($orderId)
                ->getIncrementId();
            $customerId = $order->getCustomerId();
            $currencyCode = $order->getOrderCurrencyCode();
            $currencyCreditAmount = $this->getCreditAmountData($orderId);
            if ($currencyCreditAmount > 0) {
                $baseCurrencyCode = $this->_helper->getBaseCurrencyCode();
                $creditAmount = $this->_helper->getwkconvertCurrency(
                    $currencyCode,
                    $baseCurrencyCode,
                    $currencyCreditAmount
                );
                $transferAmountData = [
                    'customerid' => $customerId,
                    'walletamount' => $creditAmount,
                    'walletactiontype' => 'credit',
                    'curr_code' => $currencyCode,
                    'curr_amount' => $currencyCreditAmount,
                    'walletnote' => __('Order id : %1 Cash Back Amount', $incrementId),
                    'sender_id' => 0,
                    'sender_type' => 1,
                    'order_id' => $orderId,
                    'status' => 1,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
                $creditedAmountModel = $this->_walletcreditAmountFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId);
                foreach ($creditedAmountModel as $model) {
                    $model->setStatus(1)->save();
                }
            }
        }
    }

    public function getCreditAmountData($orderId)
    {
        $amount = 0;
        $creditAmountModel = $this->_walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', 0);

        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $creditamount) {
                $amount = $creditamount->getAmount();
            }
        }
        return $amount;
    }
}
