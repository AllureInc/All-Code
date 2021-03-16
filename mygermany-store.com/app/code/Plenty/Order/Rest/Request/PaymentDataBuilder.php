<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plenty\Order\Helper\Data as Helper;

/**
 * Class PaymentDataBuilder
 * @package Plenty\Order\Rest\Request
 */
class PaymentDataBuilder implements PaymentDataInterface
{
    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var array
     */
    private $_request;

    /**
     * PaymentDataBuilder constructor.
     * @param Helper $helper
     * @param DateTime $dateTime
     */
    public function __construct(
        Helper $helper,
        DateTime $dateTime
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param int $paymentMethodId
     * @return $this|PaymentDataInterface
     * @throws \Exception
     */
    public function buildRequest(SalesOrderInterface $salesOrder, $paymentMethodId)
    {
        $this->_request = [];

        if (!$payment = $salesOrder->getPayment()) {
            throw new \Exception(__('Order has no payment. [Order: %1]',
                $salesOrder->getIncrementId()));
        }

        $this->setRequest(
            [
                'mage_order_id'     => $salesOrder->getIncrementId(),
                'amount'            => $payment->getAmountPaid(),
                'exchangeRatio'     => 0,
                'mopId'             => $paymentMethodId,
                'currency'          => $salesOrder->getOrderCurrencyCode(),
                'type'              => self::TYPE_CREDIT,
                'status'            => $this->_getPaymentStatus($salesOrder),
                'transactionType'   => self::TRANSACTION_TYPE_BOOKED_PAYMENT,
                // 'hash'              => hash('ripemd128', $order->getRealOrderId())
            ]
        );

        return $this;
    }

    /**
     * @param SalesOrderInterface $order
     * @return string
     */
    private function _getPaymentStatus(SalesOrderInterface $order)
    {
        $paymentStatus = self::STATUS_AWAITING_APPROVAL;
        if (null === $order->getBaseTotalDue()) {
            $paymentStatus = self::STATUS_APPROVED;
        }

        return $paymentStatus;
    }
}