<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface StockDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface StockDataInterface
{
    const PURCHASE_PRICE            = 'purchasePrice';
    const RESERVED_LISTING          = 'reservedListing';
    const RESERVED_BUNDLES          = 'reservedBundles';
    const VARIATION_ID              = 'variationId';
    const ITEM_ID                   = 'itemId';
    const WAREHOUSE_ID              = 'warehouseId';
    const PHYSICAL_STOCK            = 'physicalStock';
    const RESERVED_STOCK            = 'reservedStock';
    const NET_STOCK                 = 'netStock';
    const REORDER_LEVEL             = 'reorderLevel';
    const DELTA_REORDER_LEVEL       = 'deltaReorderLevel';
    const CREATED_AT                = 'createdAt';
    const UPDATED_AT                = 'updatedAt';
}