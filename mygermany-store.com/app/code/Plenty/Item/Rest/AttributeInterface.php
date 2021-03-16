<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

/**
 * Interface AttributeInterface
 * @package Plenty\Item\Rest
 */
interface AttributeInterface
{
    /**
     * @param $attributeId
     * @return mixed
     */
    public function getAttributeById($attributeId);

    /**
     * @param $attributeId
     * @param $valueId
     * @return mixed
     */
    public function getAttributeValue($attributeId, $valueId);

    /**
     * @param int $page
     * @param null $updatedAt
     * @param null $with
     * @param bool $addValueNames
     * @return mixed
     */
    public function getSearchAttributes(
        $page = 1,
        $updatedAt = null,
        $with = null,
        $addValueNames = false
    );

    /**
     * @param $attributeId
     * @return mixed
     */
    public function getSearchAttributeNames($attributeId);

    /**
     * @param $attributeId
     * @return mixed
     */
    public function getSearchAttributeValues($attributeId);

    /**
     * @param $valueId
     * @return mixed
     */
    public function getSearchAttributeValueNames($valueId);

    /**
     * @return mixed
     */
    public function getSearchManufacturers();

    /**
     * @param $params
     * @param null $id
     * @return mixed
     */
    public function createAttribute($params, $id = null);

    /**
     * @param $params
     * @param $attributeId
     * @param null $lang
     * @return mixed
     */
    public function createAttributeNames($params, $attributeId, $lang = null);

    /**
     * @param $params
     * @param $attributeId
     * @param null $valueId
     * @return mixed
     */
    public function createAttributeValues($params, $attributeId, $valueId = null);

    /**
     * @param $params
     * @param $valueId
     * @param null $lang
     * @return mixed
     */
    public function createAttributeValuesNames($params, $valueId, $lang = null);

    /**
     * @param int $page
     * @param null $with
     * @param null $id
     * @param null $updatedAt
     * @param null $groupId
     * @return mixed
     */
    public function getSearchProperties($page = 1, $with = null, $id = null, $updatedAt = null, $groupId = null);

    /**
     * @param $propertyId
     * @param null $selectionId
     * @param null $lang
     * @return mixed
     */
    public function getSearchPropertySelections($propertyId, $selectionId = null, $lang = null);

    /**
     * @param $params
     * @param null $propertyId
     * @return mixed
     */
    public function createProperty($params, $propertyId = null);

    /**
     * @param $propertyId
     * @param $params
     * @param null $lang
     * @return mixed
     */
    public function createPropertyName($propertyId, $params, $lang = null);

    /**
     * @param $itemId
     * @param $variationId
     * @param array $params
     * @param null $propertyId
     * @return mixed
     */
    public function createPropertyValueLink($itemId, $variationId, array $params, $propertyId = null);

    /**
     * @param $itemId
     * @param $variationId
     * @param $propertyId
     * @param array $params
     * @param null $lang
     * @return mixed
     */
    public function createPropertyValueText($itemId, $variationId, $propertyId, array $params, $lang = null);
}