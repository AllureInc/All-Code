<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

/**
 * Interface AttributeImportRepositoryInterface
 * @package Plenty\Item\Api
 */
interface AttributeImportRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Data\AttributeImportSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param $attributeCode
     * @param string $type
     * @return mixed
     */
    public function get($attributeCode, $type = 'attribute_code');

    /**
     * @param $attributeId
     * @param string $type
     * @return mixed
     */
    public function getById($attributeId, $type = 'attribute_id');

    /**
     * @param Data\Import\AttributeInterface $attribute
     * @return mixed
     */
    public function save(Data\Import\AttributeInterface $attribute);

    /**
     * @param Data\Import\AttributeInterface $attribute
     * @return mixed
     */
    public function delete(Data\Import\AttributeInterface $attribute);

    /**
     * @param $attribute
     * @param string $type
     * @return mixed
     */
    public function deleteById($attribute, $type = 'attribute_id');
}
