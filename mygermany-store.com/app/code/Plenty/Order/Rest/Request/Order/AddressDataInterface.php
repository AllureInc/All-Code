<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Plenty\Customer\Rest\AbstractData;

/**
 * Interface AddressDataInterface
 * @package Plenty\Order\Rest\Request\Order
 */
interface AddressDataInterface extends AbstractData\Contact\AddressDataInterface
{
    /**
     * @return array
     */
    public function getRequest();

    /**
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request);

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @param OrderInterface $salesOrder
     * @param OrderAddressInterface $salesOrderAddress
     * @return mixed
     */
    public function buildRequest(
        OrderInterface $salesOrder,
        OrderAddressInterface $salesOrderAddress
    );

    /**
     * @param OrderInterface $salesOrder
     * @param array $salesOrderAddresses
     * @return array
     */
    public function buildBatchRequest(OrderInterface $salesOrder, array $salesOrderAddresses);
}