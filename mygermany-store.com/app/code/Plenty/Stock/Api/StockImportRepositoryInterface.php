<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plenty\Stock\Model\StockImportRepository;

/**
 * Interface StockImportRepositoryInterface
 * @package Plenty\Stock\Api
 * @since 0.1.1
 */
interface StockImportRepositoryInterface
{
    /**
     * @param $entityId
     * @param null $field
     * @return Data\Import\InventoryInterface
     */
    public function get($entityId, $field = null);

    /**
     * @param $itemId
     * @return Data\Import\InventoryInterface
     */
    public function getBySku($sku);

    /**
     * @param $variationId
     * @return Data\Import\InventoryInterface
     */
    public function getByVariationId($variationId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Import\InventorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt($profileId = null);

    /**
     * @param Data\Import\InventoryInterface $entity
     * @return Data\Import\InventoryInterface
     */
    public function save(Data\Import\InventoryInterface $entity);

    /**
     * @param array $stockData
     * @param array $fields
     * @return StockImportRepository
     * @throws \Exception
     */
    public function saveMultiple(array $stockData, array $fields = []);

    /**
     * @param Data\Import\InventoryInterface $item
     * @return bool
     */
    public function delete(Data\Import\InventoryInterface $item);

    /**
     * @param $entityId
     * @return Data\Import\InventoryInterface
     */
    public function deleteById($entityId);
}
