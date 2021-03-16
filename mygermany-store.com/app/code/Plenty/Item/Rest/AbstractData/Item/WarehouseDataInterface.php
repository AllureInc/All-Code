<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface WarehouseDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface WarehouseDataInterface
{
    const VARIATION_ID              = 'variationId';
    const WAREHOUSE_ID              = 'warehouseId';
    const WAREHOUSE_ZONE_ID         = 'warehouseZoneId';
    const STORAGE_LOCATION_TYPE     = 'storageLocationType';
    const REORDER_LEVEL             = 'reorderLevel';
    const MAX_STOCK                 = 'maximumStock';
    const STOCK_TURNOVER_IN_DAYS    = 'stockTurnoverInDays';
    const STORAGE_LOCATION          = 'storageLocation';
    const STOCK_BUFFER              = 'stockBuffer';
    const IS_BATCH                  = 'isBatch';
    const IS_BEST_BEFORE_DATE       = 'isBestBeforeDate';
    const UPDATED_AT                = 'lastUpdateTimestamp';
    const CREATED_AT                = 'createdAt';
}