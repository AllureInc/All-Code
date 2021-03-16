<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Magento\Framework\DataObject;

/**
 * Interface ItemManagementInterface
 * @package Plenty\Item\Api\Data\Import
 */
interface ItemManagementInterface
{
    /**
     * @return array
     */
    public function getResponse();

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param null $profileId
     * @return mixed
     */
    public function getItemsLastUpdatedAt($profileId = null);

    /**
     * @return mixed
     */
    public function getVariationsLastUpdatedAt();

    /**
     * @param $profileId
     * @param null $itemId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $updatedBetween
     * @param null $variationUpdatedBetween
     * @return $this
     */
    public function collectItems(
        $profileId,
        $itemId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $updatedBetween = null,
        $variationUpdatedBetween = null
    );

    /**
     * @param null $itemId
     * @param null $variationId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $sku
     * @param null $itemName
     * @param bool $isMain
     * @param null $updatedBetween
     * @param null $relatedUpdatedBetween
     * @param null $stockWarehouseId
     * @return $this
     */
    public function collectVariations(
        $itemId = null,
        $variationId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $sku = null,
        $itemName = null,
        $isMain = false,
        $updatedBetween = null,
        $relatedUpdatedBetween = null,
        $stockWarehouseId = null
    );

    /**
     * @param $profileId
     * @param $sku
     * @param null $itemSearchFilter
     * @param null $variationSearchFilter
     * @return DataObject
     */
    public function collectBySku(
        $profileId,
        $sku,
        $itemSearchFilter = null,
        $variationSearchFilter = null
    );
}