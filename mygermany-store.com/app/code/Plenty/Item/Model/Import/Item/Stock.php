<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Stock\Collection;
use Plenty\Item\Api\Data\Import\Item\StockInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Stock
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Stock getResource()
 * @method Collection getCollection()
 */
class Stock extends ImportExportAbstract implements StockInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_stock';
    protected $_cacheTag        = 'plenty_item_import_item_stock';
    protected $_eventPrefix     = 'plenty_item_import_item_stock';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Stock::class);
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

    public function getPurchasePrice()
    {
        return $this->getData(self::PURCHASE_PRICE);
    }

    public function getReservedListing()
    {
        return $this->getData(self::RESERVED_LISTING);
    }

    public function getReservedBundles()
    {
        return $this->getData(self::RESERVED_BUNDLES);
    }

    public function getPhysicalStock()
    {
        return $this->getData(self::PHYSICAL_STOCK);
    }

    public function getReservedStock()
    {
        return $this->getData(self::RESERVED_STOCK);
    }

    public function getNetStock()
    {
        return $this->getData(self::NET_STOCK);
    }

    public function getReorderLevel()
    {
        return $this->getData(self::REORDER_LEVEL);
    }

    public function getDeltaReorderLevel()
    {
        return $this->getData(self::DELTA_REORDER_LEVEL);
    }

    public function getGoodsValue()
    {
        return $this->getData(self::GOODS_VALUE);
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
     * @return Stock
     */
    public function getVariationStock(Variation $variation) : Stock
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->addFieldToFilter(self::WAREHOUSE_ID, $variation->getMainWarehouseId())
            ->getFirstItem();
    }
}