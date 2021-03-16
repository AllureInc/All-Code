<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Api\Data\Profile;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plenty\Core\Api\Data\ProfileTypeInterface;
use Plenty\Stock\Profile\Import\Entity\Inventory;

/**
 * Interface StockImportInterface
 * @package Plenty\Stock\Api\Data\Profile
 */
interface StockImportInterface extends ProfileTypeInterface
{
    const TYPE = 'type';

    // PROCESS STAGES
    const STAGE_COLLECT_STOCK = 'collect_stock';
    const STAGE_IMPORT_STOCK = 'import_stock';

    // IMPORT CONFIG
    const XML_PATH_IS_ACTIVE_STOCK_IMPORT = 'stock_import/import_config/is_active';
    const XML_PATH_IMPORT_BATCH_SIZE = 'product_import/import_config/import_batch_size';
    const XML_PATH_REINDEX_AFTER = 'stock_import/import_config/reindex_after';

    // API CONFIG
    const XML_PATH_API_BEHAVIOUR = 'stock_import/api_config/api_behaviour';
    const XML_PATH_API_COLLECTION_SIZE = 'stock_import/api_config/api_collection_size';

    // WAREHOUSE CONFIG
    const XML_PATH_MAIN_WAREHOUSE_ID = 'stock_import/warehouse_config/main_warehouse_id';

    // CUSTOM CONFIG
    const CONFIG_IMPORT_SEARCH_CRITERIA = 'import_search_criteria';
    const CONFIG_COLLECT_SEARCH_CRITERIA = 'collect_search_criteria';

    /**
     * @return Inventory
     */
    public function collectStock();

    /**
     * @return Inventory
     * @throws \Exception
     */
    public function importStock();

    /**
     * @return bool
     */
    public function getIsActiveStockImport();

    /**
     * @return int
     */
    public function getImportBatchSize();

    /**
     * @return bool
     */
    public function getIsActiveReindexAfter();

    /**
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null);

    /**
     * @return null|array
     */
    public function getCollectSearchCriteria();

    /**
     * @return null|array
     */
    public function getCollectSearchCriteriaAllowedFields();

    /**
     * @param array $searchCriteria
     * @return $this
     */
    public function setCollectSearchCriteria(array $searchCriteria);

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getImportSearchCriteria();


    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Inventory
     */
    public function setImportSearchCriteria(SearchCriteriaInterface $searchCriteria);
}
