<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface ItemDataInterface
 * @package Plenty\Item\Rest\Response
 */
interface ItemDataInterface extends \Plenty\Item\Rest\AbstractData\ItemDataInterface
{
    /**
     * @param array $response
     * @return Collection
     */
    public function buildResponse(array $response): Collection;
}