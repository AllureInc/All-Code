<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Warehouse\Collection;
use Plenty\Item\Api\Data\Import\Item\WarehouseInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Warehouse
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Warehouse getResource()
 * @method Collection getCollection()
 */
class Warehouse extends ImportExportAbstract implements WarehouseInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_warehouse';
    protected $_cacheTag        = 'plenty_item_import_item_warehouse';
    protected $_eventPrefix     = 'plenty_item_import_item_warehouse';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Warehouse::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    public function getWarehouseZoneId()
    {
        return $this->getData(self::WAREHOUSE_ZONE_ID);
    }

    public function getStorageLocationType()
    {
        return $this->getData(self::STORAGE_LOCATION_TYPE);
    }

    public function getReorderLevel()
    {
        return $this->getData(self::REORDER_LEVEL);
    }

    public function getMaxStock()
    {
        return $this->getData(self::MAX_STOCK);
    }

    public function getStockTurnoverInDays()
    {
        return $this->getData(self::STOCK_TURNOVER_IN_DAYS);
    }

    public function getStorageLocation()
    {
        return $this->getData(self::STORAGE_LOCATION);
    }

    public function getStockBuffer()
    {
        return $this->getData(self::STOCK_BUFFER);
    }

    public function getIsBatch()
    {
        return $this->getData(self::IS_BATCH);
    }

    public function getIsBestBeforeDate()
    {
        return $this->getData(self::IS_BEST_BEFORE_DATE);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationWarehouses(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}