<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface ContactDataInterface
 * @package Plenty\Customer\Rest\Response
 */
interface ContactDataInterface extends \Plenty\Customer\Rest\AbstractData\ContactDataInterface
{
    /**
     * @param array $response
     * @return Collection
     */
    public function buildResponse(array $response): Collection;
}