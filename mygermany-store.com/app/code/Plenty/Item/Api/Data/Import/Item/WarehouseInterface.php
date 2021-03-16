<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Warehouse\Collection;

/**
 * Interface WarehouseInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface WarehouseInterface
{
    const ENTITY_ID                 = 'entity_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const WAREHOUSE_ID              = 'warehouse_id';
    const WAREHOUSE_ZONE_ID         = 'warehouse_zone_id';
    const STORAGE_LOCATION_TYPE     = 'storage_location_type';
    const REORDER_LEVEL             = 'reorder_level';
    const MAX_STOCK                 = 'max_stock';
    const STOCK_TURNOVER_IN_DAYS    = 'stock_turnover_in_days';
    const STORAGE_LOCATION          = 'storage_location';
    const STOCK_BUFFER              = 'stock_buffer';
    const IS_BATCH                  = 'is_batch';
    const IS_BEST_BEFORE_DATE       = 'is_best_before_date';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getWarehouseId();

    public function getWarehouseZoneId();

    public function getStorageLocationType();

    public function getReorderLevel();

    public function getMaxStock();

    public function getStockTurnoverInDays();

    public function getStorageLocation();

    public function getStockBuffer();

    public function getIsBatch();

    public function getIsBestBeforeDate();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationWarehouses(Variation $variation) : Collection;
}