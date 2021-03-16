<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface CategoryDataInterface
 * @package Plenty\Item\Rest\Response
 */
interface CategoryDataInterface extends \Plenty\Item\Rest\AbstractData\CategoryDataInterface
{
    /**
     * @param array $response
     * @param string $entityType
     * @return Collection
     */
    public function buildResponse(array $response, $entityType = self::CATEGORY_TYPE_ITEM): Collection;
}