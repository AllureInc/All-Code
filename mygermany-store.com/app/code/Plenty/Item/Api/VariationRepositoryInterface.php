<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;

/**
 * Interface VariationRepositoryInterface
 * @package Plenty\Item\Api
 */
interface VariationRepositoryInterface
{
    /**
     * @param $entityId
     * @return Data\Import\Item\VariationInterface
     */
    public function get($entityId);

    /**
     * @param $variationId
     * @return Data\Import\Item\VariationInterface
     */
    public function getById($variationId);

    /**
     * @param $sku
     * @return Data\Import\Item\VariationInterface
     */
    public function getBySku($sku);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Import\VariationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt();

    /**
     * @param Data\Import\Item\VariationInterface $entity
     * @return Data\Import\Item\VariationInterface
     */
    public function save(Data\Import\Item\VariationInterface $entity);

    /**
     * @param Collection $responseData
     * @return mixed
     */
    public function saveVariationCollection(Collection $responseData);

    /**
     * @param Data\Import\Item\VariationInterface $entity
     * @return bool
     */
    public function delete(Data\Import\Item\VariationInterface $entity);

    /**
     * @param $id
     * @return bool
     */
    public function deleteById($id);
}