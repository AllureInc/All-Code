<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;

/**
 * Interface OrderInterface
 * @package Plenty\Order\Rest
 */
interface OrderInterface
{
    /**
     * @param int $page
     * @param null $orderId
     * @param null $externalOrderId
     * @param null $referrerId
     * @param null $contactId
     * @param null $with
     * @param null $paymentStatus
     * @param null $updatedAtFrom
     * @param null $createdAtFrom
     * @param null $paidAtFrom
     * @param null $outgoingItemsBookedAtFrom
     * @return Collection
     */
    public function getSearchOrders(
        $page = 1,
        $orderId = null,
        $externalOrderId = null,
        $referrerId = null,
        $contactId = null,
        $with = null,
        $paymentStatus = null,
        $updatedAtFrom = null,
        $createdAtFrom = null,
        $paidAtFrom = null,
        $outgoingItemsBookedAtFrom = null
    ) : Collection;

    /**
     * @param $referrerId
     * @param null $with
     * @return Collection
     */
    public function getSearchOrdersByReferrerId($referrerId, $with = null) : Collection;

    /**
     * @param $orderId
     * @param null $with
     * @return DataObject
     */
    public function getOrderById($orderId, $with = null) : DataObject;

    /**
     * @param $externalId
     * @param null $with
     * @return DataObject
     */
    public function getOrderByExternalOrderId($externalId, $with = null) : DataObject;

    /**
     * @param $params
     * @param null $plentyOrderId
     * @return array|null
     */
    public function createOrder($params, $plentyOrderId = null) : ?array;

}