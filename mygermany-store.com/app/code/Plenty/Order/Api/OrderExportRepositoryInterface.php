<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api;

/**
 * Interface OrderExportRepositoryInterface
 * @package Plenty\Order\Api
 */
interface OrderExportRepositoryInterface
{
    /**
     * Return plenty order data for a specified order.
     *
     * @param int $orderId The order ID.
     * @return Data\Export\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\Export\OrderInterface $order
     * @return Data\Export\OrderInterface
     */
    public function save(Data\Export\OrderInterface $order);

    /**
     * @param Data\Export\OrderInterface $order
     * @return mixed
     */
    public function delete(Data\Export\OrderInterface $order);

    /**
     * @param $orderId
     * @return mixed
     */
    public function deleteById($orderId);
}
