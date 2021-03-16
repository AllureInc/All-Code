<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface PaymentDataInterface
 * @package Plenty\Order\Rest\Response
 */
interface PaymentDataInterface extends \Plenty\Order\Rest\AbstractData\PaymentDataInterface
{
    /**
     * @param array $response
     * @return Collection
     */
    public function buildResponse(array $response): Collection;
}