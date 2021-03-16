<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface AttributeDataInterface
 * @package Plenty\Item\Rest\Response
 */
interface AttributeDataInterface extends \Plenty\Item\Rest\AbstractData\AttributeDataInterface
{
    /**
     * @param array $response
     * @param string $entityType
     * @return Collection
     */
    public function buildResponse(array $response, $entityType = self::ENTITY_ATTRIBUTE): Collection;
}