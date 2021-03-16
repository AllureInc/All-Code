<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Interface ItemDataInterface
 * @package Plenty\Order\Rest\Request\Order
 */
interface ItemDataInterface extends \Plenty\Order\Rest\AbstractData\Order\ItemDataInterface
{
    /**
     * @return array
     */
    public function getRequest();

    /**
     * @param $request
     * @return $this
     */
    public function setRequest(array $request);

    /**
     * @param OrderInterface $salesOrder
     * @param OrderItemInterface $salesItem
     * @return $this
     */
    public function buildRequest(
        OrderInterface $salesOrder,
        OrderItemInterface $salesItem
    );

    /**
     * @param OrderInterface $salesOrder
     * @param null $referrerId
     * @param null $warehouseId
     * @param null $shippingProfileId
     * @return $this
     */
    public function buildBatchRequest(
        OrderInterface $salesOrder,
        $referrerId = null,
        $warehouseId = null,
        $shippingProfileId = null
    );
}