<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Export\Entity;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;

use Plenty\Core\Model\Profile;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\CategoryCollectManagementInterface;
use Plenty\Item\Api\CategoryImportRepositoryInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Api\ItemManagementInterface;
use Plenty\Item\Model\Import\Attribute;
use Plenty\Core\Model\Source\Status;

/**
 * Class Product
 * @package Plenty\Item\Profile\Export\Entity
 */
class Product extends Profile\Type\AbstractType
    implements ProductExportInterface
{
    /**
     * @var Attribute
     */
    private $_attributeCollectManagement;

    /**
     * @var CategoryCollectManagementInterface
     */
    private $_categoryCollectManagement;

    /**
     * @var CategoryImportRepositoryInterface
     */
    private $_categoryImportRepository;

    /**
     * @var ItemManagementInterface
     */
    private $_itemCollectManagement;

    /**
     * @var ProductExportManagementInterface
     */
    private $_productExportManagement;

    /**
     * @var Collection
     */
    private $_storeMappingCollection;

    /**
     * @var CollectionFactory
     */
    private $_dataCollectionFactory;

    /**
     * Product constructor.
     * @param Attribute $attributeCollectManagement
     * @param CategoryCollectManagementInterface $categoryCollectManagement
     * @param CategoryImportRepositoryInterface $categoryImportRepository
     * @param ItemManagementInterface $itemCollectManagement
     * @param ProductExportManagementInterface $productExportManagement
     * @param CollectionFactory $dataCollectionFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Profile\HistoryFactory $historyFactory
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Attribute $attributeCollectManagement,
        CategoryCollectManagementInterface $categoryCollectManagement,
        CategoryImportRepositoryInterface $categoryImportRepository,
        ItemManagementInterface $itemCollectManagement,
        ProductExportManagementInterface $productExportManagement,
        CollectionFactory $dataCollectionFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Profile\HistoryFactory $historyFactory,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_attributeCollectManagement = $attributeCollectManagement;
        $this->_categoryCollectManagement = $categoryCollectManagement;
        $this->_categoryImportRepository = $categoryImportRepository;
        $this->_itemCollectManagement = $itemCollectManagement;
        $this->_productExportManagement = $productExportManagement;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        parent::__construct($eventManager, $storeManager, $historyFactory, $dateTime, $serializer, $data);
    }

    /**
     * @return $this|mixed
     * @throws \Exception
     */
    public function execute()
    {
        $this->setIsScheduledEvent(true)
            ->_resetResponseData()
            ->_initHistory();

        if (!$this->getHistory()->getId()) {
            throw new \Exception(__('Cannot find available profile task. [Profile: %1]', $this->getProfile()->getId()));
        }

        try {
            switch ($this->getHistory()->getActionCode()) {
                case self::STAGE_COLLECT_ATTRIBUTE :
                    $this->collectAttributes();
                    break;
                case self::STAGE_COLLECT_CATEGORY :
                    $this->collectCategories();
                    break;
                case self::STAGE_EXPORT_PRODUCT :
                    $this->exportProducts();
                    break;
                default : break;
            }
        } catch (\Exception $e) {
            $this->addErrors($e->getMessage());
        }

        $this->handleHistory();
        $this->_handleProfileSchedule();

        return $this;
    }

    /**
     * @return array
     */
    public function getProcessStages()
    {
        $stages = [
            self::STAGE_COLLECT_ATTRIBUTE,
            // self::STAGE_COLLECT_CATEGORY
        ];

        if ($this->getIsActiveProductExport()) {
            array_push($stages, self::STAGE_EXPORT_PRODUCT);
        }

        return $stages;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectAttributes()
    {
        $this->_attributeCollectManagement->setProfileEntity($this);
        $this->_attributeCollectManagement->collect();

        if ($response = $this->_attributeCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Attributes have been collected.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return $this|ProductExportInterface
     * @throws \Exception
     */
    public function collectCategories()
    {
        $withFilter = 'details,clients';
        $lastUpdatedAt = null;
        if ($this->getApiBehaviour() === Profile\Config\Source\Behaviour::APPEND) {
            $lastUpdatedAt = $this->_categoryImportRepository->getLastUpdatedAt($this->getProfile()->getId());
        }

        $this->_categoryCollectManagement->setProfile($this->getProfile())
            ->setDefaultStoreId($this->getDefaultStoreMapping()->getData('mage_store_id'))
            ->setRootCategoryMapping($this->getRootCategoryMapping())
            ->execute(null, $lastUpdatedAt, $withFilter);

        if ($response = $this->_categoryCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Categories have been collected.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectItems()
    {
        $lastItemUpdatedAt =
        $lastVariationUpdatedAt = null;
        if ($this->getApiBehaviour() === Profile\Config\Source\Behaviour::APPEND) {
            $lastItemUpdatedAt = $this->_itemCollectManagement->getItemsLastUpdatedAt($this->getProfile()->getId());
            $lastVariationUpdatedAt = $this->_itemCollectManagement->getVariationsLastUpdatedAt();
        }

        if ($itemIds = $this->getItemCollectSearchCriteria()) {
            foreach ($itemIds as $itemId) {
                $this->_itemCollectManagement->collectItems(
                    $this->getProfile()->getId(),
                    $itemId,
                    $this->getApiItemSearchFilters()
                );

                if (!$itemResponse = $this->_itemCollectManagement->getResponse()) {
                    $itemResponse[] = __('Items have been collected.');
                }

                $this->_itemCollectManagement->collectVariations(
                    $itemId,
                    null,
                    $this->getApiVariationSearchFilters()
                );

                if (!$variationResponse = $this->_itemCollectManagement->getResponse()) {
                    $itemResponse[] = __('Variations have been collected.');
                }

                $this->setResponse(array_merge_recursive($itemResponse, $variationResponse));
            }

            return $this;
        }

        $this->_itemCollectManagement->collectItems(
            $this->getProfile()->getId(),
            null,
            $this->getApiItemSearchFilters(),
            $this->getFlagOne(),
            $this->getFlagTwo(),
            null,
            $lastItemUpdatedAt
        );

        if ($response = $this->_itemCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Items have been collected.'), Status::SUCCESS);
        }

        $this->_itemCollectManagement->collectVariations(
            null,
            null,
            $this->getApiVariationSearchFilters(),
            $this->getFlagOne(),
            $this->getFlagTwo(),
            null,
            null,
            null,
            null,
            null,
            $lastVariationUpdatedAt
        );

        if ($response = $this->_itemCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Variations have been collected.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function exportProducts()
    {
        if (!$this->getIsActiveProductExport()) {
            $this->addResponse(__('Product export is inactive.'), Status::SUCCESS);
            return $this;
        }

        $this->_productExportManagement->setProfile($this->getProfile())
            ->setProfileHistory($this->getHistory())
            ->setProfileEntity($this)
            ->execute();

        if ($response = $this->_productExportManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Product export has been processed.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveProductExport()
    {
        return $this->_getConfig(self::ENABLE_PRODUCT_EXPORT);
    }

    /**
     * @return array|string|null
     */
    public function getExportBehaviour()
    {
        return $this->_getConfig(self::EXPORT_BEHAVIOUR);
    }

    /**
     * @return int
     */
    public function getExportBatchSize()
    {
        return $this->_getConfig(self::EXPORT_BATCH_SIZE);
    }

    /**
     * @return bool
     */
    public function getIsActiveRequestLog()
    {
        if ($this->hasData(self::CONFIG_IS_ACTIVE_REQUEST_LOG)) {
            return $this->getData(self::CONFIG_IS_ACTIVE_REQUEST_LOG);
        }
        return $this->_getConfig(self::XML_PATH_IS_ACTIVE_REQUEST_LOG);
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsActiveRequestLog($bool)
    {
        $this->setData(self::CONFIG_IS_ACTIVE_REQUEST_LOG, $bool);
        return $this;
    }

    /**
     * @return string
     */
    public function getApiBehaviour()
    {
        return $this->_getConfig(self::API_BEHAVIOUR);
    }

    /**
     * @param $behaviour
     * @return $this
     */
    public function setApiBehaviour($behaviour)
    {
        $this->setData(self::CONFIG_API_BEHAVIOUR, $behaviour);
        return $this;
    }

    /**
     * @return int
     */
    public function getApiCollectionSize()
    {
        return $this->_getConfig(self::API_COLLECTION_SIZE);
    }

    /**
     * @param $size
     * @return $this
     */
    public function setApiCollectionSize($size)
    {
        $this->setData(self::CONFIG_API_COLLECTION_SIZE, $size);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiItemSearchFilters()
    {
        return $this->_getConfig(self::API_ITEM_SEARCH_FILTERS);
    }

    /**
     * @return string|null
     */
    public function getApiVariationSearchFilters()
    {
        return $this->_getConfig(self::API_VARIATION_SEARCH_FILTERS);
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getStoreMapping() : Collection
    {
        if ($this->_storeMappingCollection) {
            return $this->_storeMappingCollection;
        }

        $this->_storeMappingCollection = $this->_dataCollectionFactory->create();
        if (!$values = $this->_getConfig(self::STORE_MAPPING)) {
            return $this->_storeMappingCollection;
        }

        $values = $this->_serializer->unserialize($values);
        if (isset($values['__empty'])) {
            unset($values['__empty']);
        }

        foreach ($values as $value) {
            if (!isset($value['mage_store']) || !isset($value['plenty_store'])) {
                continue;
            }

            $store = $this->_storeManager->getStore($value['mage_store']);
            $website = $this->_storeManager->getWebsite($store->getWebsiteId());

            $storeMapping = new DataObject(
                [
                    self::MAGE_WEBSITE_CODE => $website->getCode(),
                    self::MAGE_WEBSITE_ID => $website->getId(),
                    self::MAGE_STORE_CODE => $store->getCode(),
                    self::MAGE_STORE_ID =>  $store->getId(),
                    self::PLENTY_STORE => $value['plenty_store'],
                    self::IS_DEFAULT_STORE    => isset($value['is_default'])
                        ? $value['is_default']
                        : false
                ]
            );
            $this->_storeMappingCollection->addItem($storeMapping);
        }

        return $this->_storeMappingCollection;
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function setStoreMapping(array $data)
    {
        // TODO: Implement setStoreMapping() method.
    }

    /**
     * @return DataObject
     * @throws \Exception
     */
    public function getDefaultStoreMapping() : DataObject
    {
        $mainStore = null;
        foreach ($this->getStoreMapping() as $store) {
            if ($mainStore = $store->getData(self::IS_DEFAULT_STORE)) {
                $mainStore = $store;
                break;
            }
        }

        if (null === $mainStore) {
            throw new \Exception(__('Default store is not set. [Trace: %1]', __METHOD__));
        }

        return $mainStore;
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getDefaultLang()
    {
        if (!$defaultStore = $this->getDefaultStoreMapping()) {
            return $defaultStore;
        }

        return $defaultStore->getData(self::PLENTY_STORE);
    }

    /**
     * @param $lang
     * @return $this|mixed
     */
    public function setDefaultLang($lang)
    {
        $this->setData(self::CONFIG_DEFAULT_LANG, $lang);
        return $this;
    }

    /**
     * @return int
     */
    public function getFlagOne()
    {
        return $this->_getConfig(self::FLAG_ONE);
    }

    /**
     * @return int
     */
    public function getFlagTwo()
    {
        return $this->_getConfig(self::FLAG_TWO);
    }

    /**
     * @param null $store
     * @return int
     */
    public function getDefaultTaxClass($store = null)
    {
        return $this->_getConfig(self::DEFAULT_TAX_CLASS, $store);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getTaxMapping($store = null)
    {
        $taxMapping = [];
        if (!$values = $this->_getConfig(self::TAX_MAPPING, $store)) {
            return $taxMapping;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_store_tax']) || !isset($value['plenty_store_tax'])) {
                continue;
            }
            $taxMapping[$value['mage_store_tax']] = $value['plenty_store_tax'];
        }

        return $taxMapping;
    }

    /**
     * @return bool
     */
    public function getIsActiveSalesPriceExport()
    {
        return $this->_getConfig(self::ENABLE_PRICE_EXPORT);
    }

    /**
     * @return bool
     */
    public function getIsActiveSalesPriceDelete()
    {
        return $this->_getConfig(self::ENABLE_PRICE_DELETE);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getPriceMapping($store = null)
    {
        $priceMapping = [];
        if (!$values = $this->_getConfig(self::PRICE_MAPPING, $store)) {
            return $priceMapping;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_price']) || !isset($value['plenty_price'])) {
                continue;
            }
            $priceMapping[$value['mage_price']] = $value['plenty_price'];
        }

        return $priceMapping;
    }

    /**
     * @return string
     */
    public function getPurchasePriceMapping()
    {
        return $this->_getConfig(self::PURCHASE_PRICE_MAPPING);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIsActiveExportStock($storeId = null)
    {
        return $this->_getConfig(self::ENABLE_STOCK_EXPORT, $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null)
    {
        return $this->_getConfig(self::MAIN_WAREHOUSE_ID, $storeId);
    }

    /**
     * @return string|null
     */
    public function getShippingOrderPicking()
    {
        return $this->_getConfig(self::SHIPPING_ORDER_PICKING);
    }

    /**
     * @return int
     */
    public function getShippingMainWarehouseId()
    {
        return $this->_getConfig(self::SHIPPING_MAIN_WAREHOUSE_ID);
    }

    /**
     * @return int
     */
    public function getShippingPalletTypeId()
    {
        return $this->_getConfig(self::SHIPPING_PALLET_TYPE_ID);
    }

    /**
     * @return float|null
     */
    public function getShippingExtraCharge1()
    {
        return $this->_getConfig(self::SHIPPING_EXTRA_CHARGE1);
    }

    /**
     * @return float|null
     */
    public function getShippingExtraCharge2()
    {
        return $this->_getConfig(self::SHIPPING_EXTRA_CHARGE2);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getDefaultAttributeSet($store = null)
    {
        return $this->_getConfig(self::DEFAULT_ATTRIBUTE_SET, $store);
    }

    /**
     * @return array
     */
    public function getNameMapping()
    {
        $names = [];
        if (!$values = $this->_getConfig(self::PRODUCT_NAME_MAPPING)) {
            return $names;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_name']) || !isset($value['plenty_name'])) {
                continue;
            }
            $names[$value['mage_name']] = $value['plenty_name'];
        }

        return $names;
    }

    /**
     * @param null $store
     * @return string
     */
    public function getShortDescriptionMapping($store = null)
    {
        return $this->_getConfig(self::SHORT_DESCRIPTION_MAPPING, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getDescriptionMapping($store = null)
    {
        return $this->_getConfig(self::DESCRIPTION_MAPPING, $store);
    }

    /**
     * @return string
     */
    public function getTechnicalDataMapping()
    {
        return $this->_getConfig(self::TECHNICAL_DATA_MAPPING);
    }

    /**
     * @return string
     */
    public function getSupplierNameMapping()
    {
        return $this->_getConfig(self::SUPPLIER_NAME_MAPPING);
    }

    /**
     * @return string
     */
    public function getSupplierItemNumberMapping()
    {
        return $this->_getConfig(self::SUPPLIER_NUMBER_MAPPING);
    }

    /**
     * @return string
     */
    public function getManufacturerMapping()
    {
        return $this->_getConfig(self::MANUFACTURER_MAPPING);
    }

    /**
     * @return bool
     */
    public function getIsActiveExportUrl()
    {
        return $this->_getConfig(self::ENABLE_EXPORT_URL);
    }

    /**
     * @return int
     */
    public function getExportUrlOption()
    {
        return $this->_getConfig(self::EXPORT_URL_OPTIONS);
    }

    /**
     * @return string
     */
    public function getDefaultWeightUnit()
    {
        return $this->_getConfig(self::DEFAULT_WEIGHT_UNIT);
    }

    /**
     * @return string|null
     */
    public function getItemWidthMapping()
    {
        return $this->_getConfig(self::ATTRIBUTE_WIDTH_MAPPING);
    }

    /**
     * @return string|null
     */
    public function getItemLengthMapping()
    {
        return $this->_getConfig(self::ATTRIBUTE_LENGTH_MAPPING);
    }

    /**
     * @return string|null
     */
    public function getItemHeightMapping()
    {
        return $this->_getConfig(self::ATTRIBUTE_HEIGHT_MAPPING);
    }

    /**
     * @return int|null
     */
    public function getDimensionsAdjustments()
    {
        return (int) $this->_getConfig(self::ATTRIBUTE_DIMENSION_ADJUSTMENT);
    }

    /**
     * @return array
     */
    public function getPropertyMapping()
    {
        $properties = [];
        if (!$values = $this->_getConfig(self::PROPERTY_MAPPING)) {
            return $properties;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_attribute']) || !isset($value['plenty_property'])) {
                continue;
            }
            $properties[$value['mage_attribute']] = $value['plenty_property'];
        }

        return $properties;
    }

    /**
     * @return array
     */
    public function getBarcodeMapping()
    {
        $barCodes = [];
        if (!$values = $this->_getConfig(self::BARCODE_MAPPING)) {
            return $barCodes;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_barcode']) || !isset($value['plenty_barcode'])) {
                continue;
            }
            $barCodes[$value['mage_barcode']] = $value['plenty_barcode'];
        }

        return $barCodes;
    }

    /**
     * @return array
     */
    public function getMarketNumberMapping()
    {
        $marketNumbers = [];
        if (!$values = $this->_getConfig(self::MARKET_NUMBER_MAPPING)) {
            return $marketNumbers;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_market_number']) || !isset($value['plenty_market_number'])) {
                continue;
            }
            $marketNumbers[$value['mage_market_number']] = $value['plenty_market_number'];
        }

        return $marketNumbers;
    }

    /**
     * @return bool
     */
    public function getIsActiveCategoryExport()
    {
        return $this->_getConfig(self::ENABLE_CATEGORY_EXPORT);
    }

    /**
     * @return array
     */
    public function getRootCategoryMapping()
    {
        $rootCategories = [];
        if (!$values = $this->_getConfig(self::ROOT_CATEGORY_MAPPING)) {
            return $rootCategories;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_category']) || !isset($value['plenty_category'])) {
                continue;
            }
            $rootCategories[$value['mage_category']] = $value['plenty_category'];
        }

        return $rootCategories;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getFallbackCategory()
    {
        return $this->_getConfig(self::XML_PATH_CATEGORY_FALLBACK);
    }

    /**
     * @return bool
     */
    public function getIsActiveExportMedia()
    {
        return $this->_getConfig(self::ENABLE_MEDIA_EXPORT);
    }

    /**
     * @return string
     */
    public function getMediaFilter()
    {
        return $this->_getConfig(self::MEDIA_FILTER);
    }

    /**
     * @return bool
     */
    public function getIsActiveExportCrossSells()
    {
        return $this->_getConfig(self::ENABLE_CROSSSELLS_EXPORT);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function getIsMultiStore()
    {
        return $this->getStoreMapping()->getSize() > 1;
    }

    /**
     * @return array
     */
    public function getProductExportSearchCriteria()
    {
        return $this->getData(self::CONFIG_EXPORT_COLLECTION_FILTER);
    }

    /**
     * @param array $filter
     * @return $this
     */
    public function setProductExportSearchCriteria(array $filter)
    {
        $this->setData(self::CONFIG_EXPORT_COLLECTION_FILTER, $filter);
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedProductExportSearchCriteria()
    {
        return ['entity_id', 'product_id', 'sku', 'item_id'];
    }

    /**
     * @return array
     */
    public function getItemCollectSearchCriteria()
    {
        return $this->getData(self::CONFIG_ITEM_COLLECT_SEARCH_CRITERIA);
    }

    /**
     * @var array $filter
     *
     * Column-value pairs:
     * [100, 101, 202]
     *
     * @return $this
     */
    public function setItemCollectSearchCriteria(array $filter)
    {
        $this->setData(self::CONFIG_ITEM_COLLECT_SEARCH_CRITERIA, $filter);
        return $this;
    }
}
