<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\Import\Item\Stock;
use Plenty\Item\Model\ResourceModel\Import\Item\Stock\Collection;

/**
 * Interface StockInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface StockInterface
{
    const ENTITY_ID                 = 'entity_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const WAREHOUSE_ID              = 'warehouse_id';
    const PURCHASE_PRICE            = 'purchase_price';
    const RESERVED_LISTING          = 'reserved_listing';
    const RESERVED_BUNDLES          = 'reserved_bundles';
    const PHYSICAL_STOCK            = 'physical_stock';
    const RESERVED_STOCK            = 'reserved_stock';
    const NET_STOCK                 = 'net_stock';
    const REORDER_LEVEL             = 'reorder_level';
    const DELTA_REORDER_LEVEL       = 'delta_reorder_level';
    const GOODS_VALUE               = 'goods_value';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getWarehouseId();

    public function getPurchasePrice();

    public function getReservedListing();

    public function getReservedBundles();

    public function getPhysicalStock();

    public function getReservedStock();

    public function getNetStock();

    public function getReorderLevel();

    public function getDeltaReorderLevel();

    public function getGoodsValue();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Stock
     */
    public function getVariationStock(Variation $variation) : Stock;
}