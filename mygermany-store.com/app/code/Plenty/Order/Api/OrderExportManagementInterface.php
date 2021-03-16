<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Plenty\Order\Api\Data\Profile\OrderExportInterface;

/**
 * Interface OrderExportManagementInterface
 * @package Plenty\Order\Api
 */
interface OrderExportManagementInterface
{
    /**
     * @return OrderExportInterface
     */
    public function getProfileEntity() : OrderExportInterface;

    /**
     * @param OrderExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(OrderExportInterface $profileEntity);

    /**
     * @return array
     */
    public function getResponse();

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return $this
     */
    public function execute();
}
