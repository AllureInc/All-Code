<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Magento\Framework\Data\Collection;

/**
 * Interface AddressInterface
 * @package Plenty\Order\Rest
 */
interface AddressInterface
{
    /**
     * @param $orderId
     * @param $relationTypeId
     * @return mixed
     */
    public function getOrderAddress($orderId, $relationTypeId);

    /**
     * @param array $request
     * @return mixed
     */
    public function createOrderAddress(array $request);
}