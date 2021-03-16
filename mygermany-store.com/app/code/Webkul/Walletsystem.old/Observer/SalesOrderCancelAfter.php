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
use Magento\Sales\Model\OrderFactory;
use \Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

class SalesOrderCancelAfter implements ObserverInterface
{
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory;
     */
    protected $_walletRecord;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    /**
     * @param OrderFactory                                $orderFactory
     * @param \Webkul\Walletsystem\Helper\Mail            $mailHelper
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WallettransactionFactory                    $wallettransaction
     * @param WalletrecordFactory                         $walletRecord
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $wallettransaction,
        WalletrecordFactory $walletRecord,
        WalletUpdateData $walletUpdateData
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_mailHelper = $mailHelper;
        $this->_helper = $helper;
        $this->_date = $date;
        $this->_walletTransaction = $wallettransaction;
        $this->_walletRecord = $walletRecord;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $orderId = $order->getEntityId();
        $incrementId = $order->getIncrementId();
        $walletAmount = 0;
        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getWalletAmount() && !$previousInvoice->isCanceled()) {
                $walletAmount = $walletAmount + $previousInvoice->getWalletAmount();
            }
        }
        if ($order->getWalletAmount()!=$walletAmount) {
            $walletAmount = $order->getWalletAmount() - $walletAmount;
        } else {
            $walletAmount = 0;
        }

        $totalCanceledAmount = (-1 * $walletAmount);
        $baseTotalCanceledAmount = $this->_helper->baseCurrencyAmount($totalCanceledAmount);
        $currencyCode = $order->getOrderCurrencyCode();
        $rowId = 0;
        $totalAmount = 0;
        $remainingAmount = 0;
        $orderItem = $order->getAllItems();
        $productIdArray = [];

        foreach ($orderItem as $value) {
            $productIdArray[] = $value->getProductId();
        }
        $walletProductId = $this->_helper->getWalletProductId();

        if (!in_array($walletProductId, $productIdArray) && $walletAmount != 0) {
            $customerId = $order->getCustomerId();
            $transferAmountData = [
                'customerid' => $customerId,
                'walletamount' => $baseTotalCanceledAmount,
                'walletactiontype' => 'credit',
                'curr_code' => $currencyCode,
                'curr_amount' => $totalCanceledAmount,
                'walletnote' => __('Order id : %1 credited amount', $incrementId),
                'sender_id' => 0,
                'sender_type' => 2,
                'order_id' => $orderId,
                'status' => 1,
                'increment_id' => $incrementId
            ];
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
        }
    }
}
