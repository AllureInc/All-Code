<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject\IdentityInterface;

use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Rest\Variation as VariationClient;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\VariationInterface;
use Plenty\Core\Model\Profile\Config\Source\Behaviour;

/**
 * Class Variation
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Variation getResource()
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection getCollection()
 *
 * @method boolean getBehaviourUpdate()
 * @method Variation setBehaviourUpdate(boolean $value)
 * @method string getBehaviour()
 * @method Variation setBehaviour(string $value)
 * @method \Plenty\Item\Profile\Import\Entity\Product getProfileEntity()
 * @method \Plenty\Item\Profile\Import\Entity\Product setProfileEntity(object $value)
 */
class Variation extends ImportExportAbstract implements VariationInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_variation';
    protected $_cacheTag        = 'plenty_item_import_item_variation';
    protected $_eventPrefix     = 'plenty_item_import_item_variation';

    /**
     * @var AttributeValueFactory
     */
    private $_attributeValueFactory;

    /**
     * @var AttributeValue
     */
    private $_attributeValueModel;

    /**
     * @var BarcodeFactory
     */
    private $_barcodeFactory;

    /**
     * @var Barcode
     */
    private $_barcodeModel;

    /**
     * @var BundleFactory
     */
    private $_bundleFactory;

    /**
     * @var Bundle
     */
    private $_bundleModel;

    /**
     * @var CategoryFactory
     */
    private $_categoryFactory;

    /**
     * @var Category
     */
    private $_categoryModel;

    /**
     * @var MarketNumberFactory
     */
    private $_marketNumberFactory;

    /**
     * @var MarketNumber
     */
    private $_marketNumberModel;

    /**
     * @var PropertyFactory
     */
    private $_propertyFactory;

    /**
     * @var Property
     */
    private $_propertyModel;

    /**
     * @var SalesPriceFactory
     */
    private $_salesPriceFactory;

    /**
     * @var SalesPrice
     */
    private $_salesPriceModel;

    /**
     * @var StockFactory
     */
    private $_stockFactory;

    /**
     * @var Stock
     */
    private $_stockModel;

    /**
     * @var SupplierFactory
     */
    private $_supplierFactory;

    /**
     * @var Supplier
     */
    private $_supplierModel;

    /**
     * @var TextsFactory
     */
    private $_textsFactory;

    /**
     * @var Texts
     */
    private $_textsModel;

    /**
     * @var WarehouseFactory
     */
    private $_warehouseFactory;

    /**
     * @var Warehouse
     */
    private $_warehouseModel;

    /**
     * Resource constructor
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Variation::class);
    }

    /**
     * Variation constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param VariationClient $variationClient
     * @param AttributeValueFactory $attributeValueFactory
     * @param BarcodeFactory $barcodeFactory
     * @param BundleFactory $bundleFactory
     * @param CategoryFactory $categoryFactory
     * @param MarketNumberFactory $marketNumberFactory
     * @param PropertyFactory $propertyFactory
     * @param SalesPriceFactory $salesPriceFactory
     * @param StockFactory $stockFactory
     * @param SupplierFactory $supplierFactory
     * @param TextsFactory $textsFactory
     * @param WarehouseFactory $warehouseFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        VariationClient $variationClient,
        AttributeValueFactory $attributeValueFactory,
        BarcodeFactory $barcodeFactory,
        BundleFactory $bundleFactory,
        CategoryFactory $categoryFactory,
        MarketNumberFactory $marketNumberFactory,
        PropertyFactory $propertyFactory,
        SalesPriceFactory $salesPriceFactory,
        StockFactory $stockFactory,
        SupplierFactory $supplierFactory,
        TextsFactory $textsFactory,
        WarehouseFactory $warehouseFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $variationClient;
        $this->_attributeValueFactory = $attributeValueFactory;
        $this->_barcodeFactory = $barcodeFactory;
        $this->_bundleFactory = $bundleFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_marketNumberFactory = $marketNumberFactory;
        $this->_propertyFactory = $propertyFactory;
        $this->_salesPriceFactory = $salesPriceFactory;
        $this->_stockFactory = $stockFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_textsFactory = $textsFactory;
        $this->_warehouseFactory = $warehouseFactory;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return VariationClient
     */
    protected function _api()
    {
        if (!$this->_api->getProfileEntity()) {
            $this->_api->setProfileEntity($this->getProfileEntity());
        }
        return $this->_api;
    }

    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    public function getMainVariationId()
    {
        return $this->getData(self::MAIN_VARIATION_ID);
    }

    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function getIsMain()
    {
        return $this->getData(self::IS_MAIN);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    public function setProductType($type)
    {
        return $this->setData(self::PRODUCT_TYPE, $type);
    }

    public function getCategoryVariationId()
    {
        return $this->getData(self::CATEGORY_VARIATION_ID);
    }

    public function getMarketVariationId()
    {
        return $this->getData(self::MARKET_VARIATION_ID);
    }

    public function getClientVariationId()
    {
        return $this->getData(self::CLIENT_VARIATION_ID);
    }

    public function getSalesPriceVariationId()
    {
        return $this->getData(self::SALES_PRICE_VARIATION_ID);
    }

    public function getSupplierVariationId()
    {
        return $this->getData(self::SUPPLIER_VARIATION_ID);
    }

    public function getWarehouseVariationId()
    {
        return $this->getData(self::WAREHOUSE_VARIATION_ID);
    }

    public function getPropertyVariationId()
    {
        return $this->getData(self::PROPERTY_VARIATION_ID);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getModel()
    {
        return $this->getData(self::MODEL);
    }

    public function getParentVariationId()
    {
        return $this->getData(self::PARENT_VARIATION_ID);
    }

    public function getParentVariationQty()
    {
        return $this->getData(self::PARENT_VARIATION_QTY);
    }

    public function getAvailability()
    {
        return $this->getData(self::AVAILABILITY);
    }

    public function getFlagOne()
    {
        return $this->getData(self::FLAG_ONE);
    }

    public function getFlagTwo()
    {
        return $this->getData(self::FLAG_TWO);
    }

    public function getEstimatedAvailableAt()
    {
        return $this->getData(self::ESTIMATED_AVAILABLE_AT);
    }

    public function getPurchasePrice()
    {
        return $this->getData(self::PURCHASE_PRICE);
    }

    public function getRelatedUpdatedAt()
    {
        return $this->getData(self::RELATED_UPDATED_AT);
    }

    public function getPriceCalculationId()
    {
        return $this->getData(self::PRICE_CALCULATION_ID);
    }

    public function getPicking()
    {
        return $this->getData(self::PICKING);
    }

    public function getStockLimitation()
    {
        return $this->getData(self::STOCK_LIMITATION);
    }

    public function getIsVisibleIfNetStockIsPositive()
    {
        return $this->getData(self::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE);
    }

    public function getIsInvisibleIfNetStockIsNotPositive()
    {
        return $this->getData(self::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE);
    }

    public function getIsAvailableIfNetStockIsPositive()
    {
        return $this->getData(self::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE);
    }

    public function getIsUnavailableIfNetStockIsNotPositive()
    {
        return $this->getData(self::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE);
    }

    public function getMainWarehouseId()
    {
        return $this->getData(self::MAIN_WAREHOUSE_ID);
    }

    public function getMaxOrderQty()
    {
        return $this->getData(self::MAX_ORDER_QTY);
    }

    public function getMinOrderQty()
    {
        return $this->getData(self::MIN_ORDER_QTY);
    }

    public function getIntervalOrderQty()
    {
        return $this->getData(self::INTERVAL_ORDER_QTY);
    }

    public function getAvailableUnit()
    {
        return $this->getData(self::AVAILABLE_UNTIL);
    }

    public function getReleasedAt()
    {
        return $this->getData(self::RELEASED_AT);
    }

    public function getUnitCombinationId()
    {
        return $this->getData(self::UNIT_COMBINATION_ID);
    }

    public function getWeightG()
    {
        return $this->getData(self::WEIGHT_G);
    }

    public function getWeightNetG()
    {
        return $this->getData(self::WEIGHT_NET_G);
    }

    public function getWidthMm()
    {
        return $this->getData(self::WIDTH_MM);
    }

    public function getLengthMm()
    {
        return $this->getData(self::LENGTH_MM);
    }

    public function getHeightMm()
    {
        return $this->getData(self::HEIGHT_MM);
    }

    public function getExtraShippingCharge1()
    {
        return $this->getData(self::EXTRA_SHIPPING_CHARGES1);
    }

    public function getExtraShippingCharge2()
    {
        return $this->getData(self::EXTRA_SHIPPING_CHARGES2);
    }

    public function getUnitsContained()
    {
        return $this->getData(self::UNITS_CONTAINED);
    }

    public function getPalletTypeId()
    {
        return $this->getData(self::PALLET_TYPE_ID);
    }

    public function getPackingUnits()
    {
        return $this->getData(self::PACKING_UNITS);
    }

    public function getPackingUnitTypeId()
    {
        return $this->getData(self::PACKING_UNITS_TYPE_ID);
    }

    public function getTransportationCosts()
    {
        return $this->getData(self::TRANSPORTATION_COSTS);
    }

    public function getStorageCosts()
    {
        return $this->getData(self::STORAGE_COSTS);
    }

    public function getCustoms()
    {
        return $this->getData(self::CUSTOMS);
    }

    public function getOperationCosts()
    {
        return $this->getData(self::OPERATION_COSTS);
    }

    public function getVatId()
    {
        return $this->getData(self::VAT_ID);
    }

    public function getBundleType()
    {
        return $this->getData(self::BUNDLE_TYPE);
    }

    public function setBundleType($type)
    {
        $this->setData(self::BUNDLE_TYPE, $type);
        return $this;
    }

    public function getAutoClientVisibility()
    {
        return $this->getData(self::AUTO_CLIENT_VISIBILITY);
    }

    public function getIsHiddenInCategoryList()
    {
        return $this->getData(self::IS_HIDDEN_IN_CATEGORY_LIST);
    }

    public function getDefaultShippingCosts()
    {
        return $this->getData(self::DEFAULT_SHIPPING_COSTS);
    }

    public function getCanShowUnitPrice()
    {
        return $this->getData(self::CAN_SHOW_UNIT_PRICE);
    }

    public function getMovingAveragePrice()
    {
        return $this->getData(self::MOVING_AVERAGE_PRICE);
    }

    public function getAutoListVisibility()
    {
        return $this->getData(self::AUTO_LIST_VISIBILITY);
    }

    public function getIsVisibleInListIfNetStockIsPositive()
    {
        return $this->getData(self::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE);
    }

    public function getIsInvisibleInListIfNetStockIsNotPositive()
    {
        return $this->getData(self::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE);
    }

    public function getSingleItemCount()
    {
        return $this->getData(self::SINGLE_ITEM_COUNT);
    }

    public function getSalesRank()
    {
        return $this->getData(self::SALES_RANK);
    }

    public function getProperties()
    {
        return $this->getData(self::PROPERTIES);
    }

    public function getVariationClients()
    {
        return $this->getData(self::VARIATION_CLIENTS);
    }

    public function getVariationMarkets()
    {
        return $this->getData(self::VARIATION_MARKETS);
    }

    public function getVariationDefaultCategory()
    {
        return $this->getData(self::VARIATION_DEFAULT_CATEGORY);
    }

    public function getVariationSkus()
    {
        return $this->getData(self::VARIATION_SKUS);
    }

    public function getVariationAdditionalSkus()
    {
        return $this->getData(self::VARIATION_ADDITIONAL_SKUS);
    }

    public function getVariationUnit()
    {
        return $this->getData(self::VARIATION_UNIT);
    }

    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
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

    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return VariationInterface
     */
    public function getItemVariation(\Plenty\Item\Model\Import\Item $item)
    {
        return $this->getResource()
            ->load($this, $item->getVariationId(), self::VARIATION_ID);
    }

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemVariationCollection(\Plenty\Item\Model\Import\Item $item)
    {
        return $this->getCollection()
            ->addFieldToFilter(self::ITEM_ID, $item->getItemId())
            ->load();
    }

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemLinkedVariations(\Plenty\Item\Model\Import\Item $item)
    {
        return $this->getCollection()
            // ->addFieldToFilter(self::ITEM_ID, $item->getItemId())
            // ->addFieldToFilter(self::IS_MAIN, false)
            ->addFieldToFilter(self::MAIN_VARIATION_ID, $item->getVariationId())
            ->load();
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue\Collection
     */
    public function getVariationAttributeValues()
    {
        return $this->_getAttributeValueModel()
            ->getVariationAttributeValues($this);
    }

    /**
     * @param null $barcodeId
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Barcode\Collection
     */
    public function getVariationBarcodes($barcodeId = null)
    {
        return $this->_getBarcodeModel()
            ->getVariationBarcodes($this, $barcodeId);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Bundle\Collection
     */
    public function getVariationBundleComponents()
    {
        return $this->_getBundleModel()
            ->getBundleComponents($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Category\Collection
     */
    public function getVariationCategories()
    {
        return  $this->_getCategoryModel()
            ->getVariationCategories($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\Collection
     */
    public function getVariationMarketNumbers()
    {
        return $this->_getMarketNumberModel()
            ->getVariationMarketNumbers($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection
     */
    public function getVariationProperties()
    {
        return $this->_getPropertyModel()
            ->getVariationProperties($this);
    }

    /**
     * @param null $priceId
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\Collection
     */
    public function getVariationSalesPrices($priceId = null)
    {
        return $this->_getSalesPriceModel()
            ->getVariationSalesPrices($this, $priceId);
    }

    /**
     * @return Stock
     */
    public function getVariationStock()
    {
        return $this->_getStockModel()
            ->getVariationStock($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Supplier\Collection
     */
    public function getVariationSuppliers()
    {
        return $this->_getSupplierModel()
            ->getVariationSuppliers($this);
    }

    /**
     * @param null $lang
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Texts\Collection
     */
    public function getVariationTexts($lang = null)
    {
        return $this->_getTextsModel()
                ->getVariationTexts($this, $lang);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Warehouse\Collection
     */
    public function getVariationWarehouses()
    {
        return $this->_getWarehouseModel()
            ->getVariationWarehouses($this);
    }

    /**
     * @return AttributeValue
     */
    private function _getAttributeValueModel()
    {
        if (!$this->_attributeValueModel) {
            $this->_attributeValueModel = $this->_attributeValueFactory->create();
        }
        return $this->_attributeValueModel;
    }

    /**
     * @return Barcode
     */
    private function _getBarcodeModel()
    {
        if (!$this->_barcodeModel) {
            $this->_barcodeModel = $this->_barcodeFactory->create();
        }
        return $this->_barcodeModel;
    }

    /**
     * @return Bundle
     */
    private function _getBundleModel()
    {
        if (!$this->_bundleModel) {
            $this->_bundleModel = $this->_bundleFactory->create();
        }
        return $this->_bundleModel;
    }

    /**
     * @return Category
     */
    private function _getCategoryModel()
    {
        if (!$this->_categoryModel) {
            $this->_categoryModel = $this->_categoryFactory->create();
        }
        return $this->_categoryModel;
    }

    /**
     * @return MarketNumber
     */
    private function _getMarketNumberModel()
    {
        if (!$this->_marketNumberModel) {
            $this->_marketNumberModel = $this->_marketNumberFactory->create();
        }
        return $this->_marketNumberModel;
    }

    /**
     * @return Property
     */
    private function _getPropertyModel()
    {
        if (!$this->_propertyModel) {
            $this->_propertyModel = $this->_propertyFactory->create();
        }
        return $this->_propertyModel;
    }

    /**
     * @return SalesPrice
     */
    private function _getSalesPriceModel()
    {
        if (!$this->_salesPriceModel) {
            $this->_salesPriceModel = $this->_salesPriceFactory->create();
        }
        return $this->_salesPriceModel;
    }

    /**
     * @return Stock
     */
    private function _getStockModel()
    {
        if (!$this->_stockModel) {
            $this->_stockModel = $this->_stockFactory->create();
        }
        return $this->_stockModel;
    }

    /**
     * @return Supplier
     */
    private function _getSupplierModel()
    {
        if (!$this->_supplierModel) {
            $this->_supplierModel = $this->_supplierFactory->create();
        }
        return $this->_supplierModel;
    }

    /**
     * @return Texts
     */
    private function _getTextsModel()
    {
        if (!$this->_textsModel) {
            $this->_textsModel = $this->_textsFactory->create();
        }
        return $this->_textsModel;
    }

    /**
     * @return Warehouse
     */
    private function _getWarehouseModel()
    {
        if (!$this->_warehouseModel) {
            $this->_warehouseModel = $this->_warehouseFactory->create();
        }
        return $this->_warehouseModel;
    }

    private function _getLangParams()
    {
        return null;
        $lang = null;
        return null;
        if ($this->isMultiStore()) {

            $stores = array();
            foreach ($this->_helper()->getStoreMapping() as $store) {
                $stores[] = $store['plenty_lang'];
            }

            $lang = implode(',', $stores);
        }

        return $lang;
    }
}