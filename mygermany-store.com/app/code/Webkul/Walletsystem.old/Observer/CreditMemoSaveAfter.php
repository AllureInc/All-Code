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
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

class CreditMemoSaveAfter implements ObserverInterface
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
     * @var WallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $_walletRecord;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $_walletcreditAmountFactory;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;

    /**
     * @param OrderFactory                                $orderFactory
     * @param \Webkul\Walletsystem\Helper\Mail            $mailHelper
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WallettransactionFactory                    $walletTransaction
     * @param WalletrecordFactory                         $walletRecord
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecord,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_mailHelper = $mailHelper;
        $this->_helper = $helper;
        $this->_date = $date;
        $this->_walletTransaction = $walletTransaction;
        $this->_walletRecord = $walletRecord;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
        $this->_walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getCreditmemo()->getOrderId();
        $order = $this->_orderFactory->create()->load($orderId);
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $params = $this->_request->getParams();
            $doOffline = $params['creditmemo']['do_offline'];
            if ($doOffline == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        "You can not do online refund for this order, do the offline refund".
                        " all the refunded amount will be transferred to customer's wallet"
                    )
                );
            }
            $rowId = 0;
            $baserefundTotalAmount = 0;
            $refundTotalAmount = 0;
            $totalAmount = 0;
            $remainingAmount = 0;
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $baserefundTotalAmount = $creditmemo->getBaseGrandTotal();
            $refundTotalAmount = $creditmemo->getGrandTotal();
            $baserefundTotalAmount = $this->getDeductCashBackRefundAmount($baserefundTotalAmount, $orderId);
            $flag = 0;
            $walletProductId = $this->_helper->getWalletProductId();
            $currencyCode = $order->getOrderCurrencyCode();
            $baseCurrencyCode = $this->_helper->getBaseCurrencyCode();
            $refundTotalAmount = $this->_helper->getwkconvertCurrency(
                $currencyCode,
                $baseCurrencyCode,
                $baserefundTotalAmount
            );

            $incrementId = $order->getIncrementId();
            $orderItem = $order->getAllItems();
            $productIdArray = [];
            foreach ($orderItem as $value) {
                $productIdArray[] = $value->getProductId();
            }
            if (!in_array($walletProductId, $productIdArray)) {
                $this->updateWalletAmount($baserefundTotalAmount, $refundTotalAmount, $order, 'credit');
            } else {
                $this->deductWalletAmountProduct($baserefundTotalAmount, $refundTotalAmount, $order);
            }
        }
    }

    public function deductWalletAmountProduct($baseAmount, $amount, $order)
    {
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $remainingAmount = $this->_helper->getWalletTotalAmount($customerId);
            if ($remainingAmount <= $baseAmount) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        "You can not generate credit memo of this order,".
                        " refunded amount is not available in customer's wallet"
                    )
                );
            } else {
                $this->updateWalletAmount($baseAmount, $amount, $order, 'debit');
            }
        }
    }

    public function updateWalletAmount($baserefundTotalAmount, $refundTotalAmount, $order, $action)
    {
        $customerId = $order->getCustomerId();
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $incrementId = $order->getIncrementId();

        $transferAmountData = [
            'customerid' => $customerId,
            'walletamount' => $baserefundTotalAmount,
            'walletactiontype' => $action,
            'curr_code' => $currencyCode,
            'curr_amount' => $refundTotalAmount,
            'walletnote' => __('Order id : %1, %2ed amount', $incrementId, $action),
            'sender_id' => 0,
            'sender_type' => 2,
            'order_id' => $orderId,
            'status' => 1,
            'increment_id' => $incrementId
        ];
        if ($action=='credit') {
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
        } else {
            $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
        }
    }
    public function getDeductCashBackRefundAmount($refundOrderAmount, $orderId)
    {
        $creditAmountModelCollection = $this->_walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', 0);
        if ($creditAmountModelCollection->getSize()) {
            foreach ($creditAmountModelCollection as $creditamount) {
                $rowId = $creditamount->getEntityId();
                $creditAmountModel = $this->_walletcreditAmountFactory->create()
                    ->load($rowId);
                $amount = $creditAmountModel->getAmount();
                $creditAmountModel->setRefundAmount($amount);
                $creditAmountModel->setStatus(1);
                $creditAmountModel->save();
            }
        } else {
            $refundAmount = 0;
            $amount = 0;
            $creditAmountModel = $this->_walletcreditAmountFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('status', 1);

            if ($creditAmountModel->getSize()) {
                foreach ($creditAmountModel as $creditamount) {
                    $refundAmount = $creditamount->getRefundAmount();
                    $amount = $creditamount->getAmount();
                }
            }
            if ($amount == $refundAmount) {
                return $refundOrderAmount;
            } else {
                $leftAmount = $amount - $refundAmount;
                if ($refundOrderAmount >= $leftAmount) {
                    $finalRefundAmount = $refundOrderAmount - $leftAmount;
                    $this->updateRefundAmount($leftAmount + $refundAmount, $orderId);
                    return $finalRefundAmount;
                } else {
                    $this->updateRefundAmount($refundOrderAmount + $refundAmount, $orderId);
                    return 0;
                }
            }
        }
        return $refundOrderAmount;
    }
    public function updateRefundAmount($amount, $orderId)
    {
        $creditAmountModel = $this->_walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', 1);
        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $amountModel) {
                $amountModel->setRefundAmount($amount);
                $amountModel->save();
            }
        }
    }
}
