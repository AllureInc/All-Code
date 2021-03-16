<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\Data\Collection;

/**
 * Interface CategoryInterface
 * @package Plenty\Item\Rest
 */
interface CategoryInterface
{
    /**
     * Search category collection per page
     * @see Plenty_Item_Helper_Data::getCategoryCollectionUrl()
     * @see https://developers.plentymarkets.com/rest-doc/category_category/details#list-categories
     *
     * @param int $page
     * @param null $categoryId
     * @param null $updatedAt
     * @param null $with
     * @param null $type
     * @param null $lang
     * @param null $parentId
     * @param null $name
     * @param null $level
     * @return Collection
     */
    public function getSearchCategory(
        $page = 1,
        $categoryId = null,
        $updatedAt = null,
        $with = null,
        $type = null,
        $lang = null,
        $parentId = null,
        $name = null,
        $level = null
    );

    /**
     * @param $itemId
     * @param $variationId
     * @param null $categoryId
     * @return mixed
     */
    public function getVariationCategory($itemId, $variationId, $categoryId = null);

    /**
     * @param $itemId
     * @param $variationId
     * @return array
     */
    public function getVariationDefaultCategory($itemId, $variationId);

    /**
     * @param array $params
     * @param null $itemId
     * @param null $variationId
     * @param null $categoryId
     * @return array
     */
    public function linkCategoryToVariation(array $params, $itemId = null, $variationId = null, $categoryId = null);

}