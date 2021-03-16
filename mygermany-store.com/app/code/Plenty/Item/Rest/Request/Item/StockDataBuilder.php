<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request\Item;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\Stock\CollectionFactory;
use Plenty\Item\Helper;

/**
 * Class StockDataBuilder
 * @package Plenty\Item\Rest\Request\Item
 */
class StockDataBuilder implements StockDataInterface
{
    /**
     * @var Helper\Data
     */
    private $_helper;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var ProductExportInterface
     */
    private $_profileEntity;

    /**
     * @var CollectionFactory
     */
    private $_stockCollectionFactory;

    /**
     * @var StockRegistryInterface
     */
    private $_stockRegistry;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * StockDataBuilder constructor.
     * @param Helper\Data $itemHelper
     * @param CollectionFactory $salesPriceCollectionFactory
     * @param StockRegistryInterface $stockRegistry
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Helper\Data $itemHelper,
        CollectionFactory $salesPriceCollectionFactory,
        StockRegistryInterface $stockRegistry,
        DateTime $dateTime,
        StoreManagerInterface $storeManager
    ) {
        $this->_helper = $itemHelper;
        $this->_stockCollectionFactory = $salesPriceCollectionFactory;
        $this->_stockRegistry = $stockRegistry;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return ProductExportInterface
     * @throws \Exception
     */
    public function getProfileEntity() : ProductExportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }
        return $this->_profileEntity;
    }

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity)
    {
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param CatalogProduct $product
     * @return array
     * @throws \Exception
     */
    public function buildRequest(CatalogProduct $product)
    {
        $this->_request = [];

        if ($product->getTypeId() == ConfigurableProduct::TYPE_CODE
            || $product->getTypeId() == CatalogProduct\Type::TYPE_BUNDLE
        ) {
            return $this->_request;
        }

        if (!$stockItem = $product->getData(ProductExportManagementInterface::STOCK_ITEM_OBJ)) {
            $stockItem = $this->_stockRegistry->getStockItem($product->getId());
        }

        $this->_request = [
            'quantity' => $stockItem->getQty()
                ? $stockItem->getQty()
                : 0,
            'warehouseId' => $this->getProfileEntity()->getMainWarehouseId(),
            'storageLocationId' => 0,
            'reasonId' => 301, /** @see https://developers.plentymarkets.com/rest-doc/item_variation_stock/details#correct-stock */
        ];

        return $this->_request;
    }
}