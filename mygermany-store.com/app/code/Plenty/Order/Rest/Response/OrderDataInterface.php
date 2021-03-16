<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface OrderDataInterface
 * @package Plenty\Order\Rest\Response
 */
interface OrderDataInterface extends \Plenty\Order\Rest\AbstractData\OrderDataInterface
{
    /**
     * @param array $response
     * @return Collection
     */
    public function buildResponse(array $response): Collection;
}