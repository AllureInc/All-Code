<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\Import;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;

use Plenty\Stock\Model\ImportExportAbstract;
use Plenty\Stock\Model\ResourceModel;
use Plenty\Stock\Api\Data\Import\InventoryInterface;

/**
 * Class Inventory
 * @package Plenty\Stock\Model\Import
 *
 * @method ResourceModel\Import\Inventory getResource()
 * @method ResourceModel\Import\Inventory\Collection getCollection()
 */
class Inventory extends ImportExportAbstract
    implements InventoryInterface, IdentityInterface
{
    const CACHE_TAG             = 'plenty_stock_import_inventory';

    /**
     * @var string
     */
    protected $_cacheTag        = 'plenty_stock_import_inventory';

    /**
     * @var string
     */
    protected $_eventPrefix     = 'plenty_stock_import_inventory';

    /**
     * @var array
     */
    protected $_importResult;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var
     */
    protected $_stockStateInterface;

    /**
     * @var StockRegistryInterface|null
     */
    protected $_stockRegistry;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Import\Inventory::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int|null
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->setData(self::PROFILE_ID, $profileId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->setData(self::ITEM_ID, $itemId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    /**
     * @param $variationId
     * @return $this
     */
    public function setVariationId($variationId)
    {
        $this->setData(self::VARIATION_ID, $variationId);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * @param $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId)
    {
        $this->setData(self::WAREHOUSE_ID, $warehouseId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStockPhysical()
    {
        return $this->getData(self::STOCK_PHYSICAL);
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setStockPhysical($qty)
    {
        $this->setData(self::STOCK_PHYSICAL, $qty);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReservedStock()
    {
        return $this->getData(self::RESERVED_STOCK);
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setReservedStock($qty)
    {
        $this->setData(self::RESERVED_STOCK, $qty);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReservedEbay()
    {
        return $this->getData(self::RESERVED_EBAY);
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setReservedEbay($qty)
    {
        $this->setData(self::RESERVED_EBAY, $qty);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReorderDelta()
    {
        return $this->getData(self::REORDER_DELTA);
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setReorderDelta($qty)
    {
        $this->setData(self::REORDER_DELTA, $qty);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStockNet()
    {
        return $this->getData(self::STOCK_NET);
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setStockNet($qty)
    {
        $this->setData(self::STOCK_NET, $qty);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param $collectedAt
     * @return $this
     */
    public function setCollectedAt($collectedAt)
    {
        $this->setData(self::COLLECTED_AT, $collectedAt);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param $processedAt
     * @return $this
     */
    public function setProcessedAt($processedAt)
    {
        $this->setData(self::PROCESSED_AT, $processedAt);
        return $this;
    }
}