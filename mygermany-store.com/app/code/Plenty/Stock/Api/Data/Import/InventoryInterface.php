<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Api\Data\Import;

use Plenty\Stock\Model\Import\Inventory;

/**
 * Interface InventoryInterface
 * @package Plenty\Stock\Api\Data\Import
 */
interface InventoryInterface
{
    const ENTITY_ID                 = 'entity_id';
    const PROFILE_ID                = 'profile_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const PRODUCT_ID                = 'product_id';
    const SKU                       = 'sku';
    const WAREHOUSE_ID              = 'warehouse_id';
    const STOCK_PHYSICAL            = 'stock_physical';
    const RESERVED_STOCK            = 'reserved_stock';
    const RESERVED_EBAY             = 'reversed_ebay';
    const REORDER_DELTA             = 'reorder_delta';
    const STOCK_NET                 = 'stock_net';
    const REORDERED                 = 'reordered';
    const RESERVE_BUNDLE            = 'reserve_bundle';
    const AVERAGE_PURCHASE_PRICE    = 'average_purchase_price';
    const STATUS                    = 'status';
    const MESSAGE                   = 'message';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';
    const PROCESSED_AT              = 'processed_at';

    const PLENTY_ITEM_ID = 'plenty_item_id';
    const PLENTY_VARIATION_ID = 'plenty_variation_id';

    /**
     * @return null|int
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getProfileId();

    /**
     * @param $profileId
     * @return Inventory
     */
    public function setProfileId($profileId);

    /**
     * @return int|null
     */
    public function getItemId();

    /**
     * @param $itemId
     * @return Inventory
     */
    public function setItemId($itemId);

    /**
     * @return int|null
     */
    public function getVariationId();

    /**
     * @param $variationId
     * @return Inventory
     */
    public function setVariationId($variationId);

    /**
     * @return null|string
     */
    public function getSku();

    /**
     * @param $sku
     * @return Inventory
     */
    public function setSku($sku);

    /**
     * @return null|int
     */
    public function getProductId();

    /**
     * @param $productId
     * @return Inventory
     */
    public function setProductId($productId);

    /**
     * @return int|null
     */
    public function getWarehouseId();

    /**
     * @param $warehouseId
     * @return Inventory
     */
    public function setWarehouseId($warehouseId);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param $status
     * @return Inventory
     */
    public function setStatus($status);

    /**
     * @return int|null
     */
    public function getStockPhysical();

    /**
     * @param $qty
     * @return Inventory
     */
    public function setStockPhysical($qty);

    /**
     * @return int|null
     */
    public function getReservedStock();

    /**
     * @param $qty
     * @return Inventory
     */
    public function setReservedStock($qty);

    /**
     * @return int|null
     */
    public function getReservedEbay();

    /**
     * @param $qty
     * @return Inventory
     */
    public function setReservedEbay($qty);

    /**
     * @return int|null
     */
    public function getReorderDelta();

    /**
     * @param $qty
     * @return Inventory
     */
    public function setReorderDelta($qty);

    /**
     * @return int|null
     */
    public function getStockNet();

    /**
     * @param $qty
     * @return Inventory
     */
    public function setStockNet($qty);

    /**
     * @return int|null
     */
    public function getMessage();

    /**
     * @param $message
     * @return Inventory
     */
    public function setMessage($message);

    /**
     * @return int|null
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return Inventory
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int|null
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return Inventory
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return int|null
     */
    public function getCollectedAt();

    /**
     * @param $collectedAt
     * @return Inventory
     */
    public function setCollectedAt($collectedAt);

    /**
     * @return int|null
     */
    public function getProcessedAt();

    /**
     * @param $processedAt
     * @return Inventory
     */
    public function setProcessedAt($processedAt);
}