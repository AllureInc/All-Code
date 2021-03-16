<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;

/**
 * Interface VariationInterface
 * @package Plenty\Item\Rest
 */
interface VariationInterface
{
    /**
     * @param int $page
     * @param null $itemId
     * @param null $variationId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $sku
     * @param null $itemName
     * @param bool $isMain
     * @param null $relatedUpdatedBetween
     * @param null $updatedBetween
     * @param null $stockWarehouseId
     * @return Collection
     */
    public function getSearchVariations(
        $page = 1,
        $itemId = null,
        $variationId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $sku = null,
        $itemName = null,
        $isMain = false,
        $relatedUpdatedBetween = null,
        $updatedBetween = null,
        $stockWarehouseId = null
    ) : Collection;

    /**
     * @param $sku
     * @return DataObject
     */
    public function getVariationBySku($sku) : DataObject;

    /**
     * @param array $params
     * @param $itemId
     * @param null $variationId
     * @return mixed
     */
    public function createVariation(array $params, $itemId, $variationId = null);

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function getVariationBundleComponents($itemId, $variationId);

    /**
     * @param $itemId
     * @param $variationId
     * @param null $lang
     * @return mixed
     */
    public function getVariationDescriptions($itemId, $variationId, $lang = null);

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $bundleId
     * @param bool $delete
     * @return mixed
     */
    public function createVariationBundle(
        array $params,
        $itemId,
        $variationId,
        $bundleId = null,
        $delete = false
    );

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $marketIdentNumberId
     * @return mixed
     */
    public function createVariationMarketNumbers(array $params, $itemId, $variationId, $marketIdentNumberId = null);

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $priceId
     * @return mixed
     */
    public function createVariationSalesPrices(array $params, $itemId, $variationId, $priceId = null);

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function createVariationStock(array $params, $itemId, $variationId);

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function getVariationProperties($itemId, $variationId);
}