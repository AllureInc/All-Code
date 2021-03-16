<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Plenty\Order\Api\Data\Export\OrderInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class SalesOrderPlaceAfter
 * @package Plenty\Order\Observer
 */
class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return;
        }

        $this->_registerPlentyOrder($order);

        return;
    }

    /**
     * @param Order $order
     * @return Order
     */
    private function _registerPlentyOrder(Order $order)
    {
        $order->setData(OrderInterface::PLENTY_ORDER_STATUS, Status::PENDING);
        return $order;
    }
}