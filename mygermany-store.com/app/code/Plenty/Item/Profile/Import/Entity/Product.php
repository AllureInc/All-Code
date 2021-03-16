<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Import\Entity;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;

use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Api\CategoryCollectManagementInterface;
use Plenty\Item\Api\CategoryImportManagementInterface;
use Plenty\Item\Api\CategoryImportRepositoryInterface;
use Plenty\Item\Api\ProductImportManagementInterface;
use Plenty\Item\Api\ItemManagementInterface;
use Plenty\Item\Model\Import\Attribute;
use Plenty\Item\Model\Import\Item;
use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;

/**
 * Class Product
 * @package Plenty\Item\Profile\Import\Entity
 */
class Product extends Profile\Type\AbstractType
    implements ProductImportInterface
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
     * @var CategoryImportManagementInterface
     */
    private $_categoryImportManagement;

    /**
     * @var ProductImportManagementInterface
     */
    private $_productImportManagement;

    /**
     * @var CategoryImportRepositoryInterface
     */
    private $_categoryImportRepository;

    /**
     * @var ItemManagementInterface
     */
    private $_itemCollectManagement;

    /**
     * @var Item
     */
    private $_itemFactory;

    /**
     * @var Variation
     */
    private $_variationFactory;

    /**
     * @var Collection
     */
    private $_storeMappingCollection;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * Product constructor.
     * @param Attribute $attributeCollectManagement
     * @param CategoryCollectManagementInterface $categoryCollectManagement
     * @param CategoryImportManagementInterface $categoryImportManagement
     * @param CategoryImportRepositoryInterface $categoryImportRepository
     * @param ProductImportManagementInterface $productImportManagement
     * @param ItemManagementInterface $itemCollectManagement
     * @param Item $itemFactory
     * @param Variation $variationFactory
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
        CategoryImportManagementInterface $categoryImportManagement,
        CategoryImportRepositoryInterface $categoryImportRepository,
        ProductImportManagementInterface $productImportManagement,
        ItemManagementInterface $itemCollectManagement,
        Item $itemFactory,
        Variation $variationFactory,
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
        $this->_categoryImportManagement = $categoryImportManagement;
        $this->_categoryImportRepository = $categoryImportRepository;
        $this->_itemCollectManagement = $itemCollectManagement;
        $this->_productImportManagement = $productImportManagement;
        $this->_itemFactory = $itemFactory;
        $this->_variationFactory = $variationFactory;
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
                case self::STAGE_IMPORT_CATEGORY :
                    $this->importCategories();
                    break;
                case self::STAGE_COLLECT_ITEM :
                    $this->collectItems();
                    break;
                case self::STAGE_IMPORT_PRODUCT :
                    $this->importProducts();
                    break;
                default : break;
            }
        } catch (\Exception $e) {
            $this->addResponse($e->getMessage(), Status::ERROR);
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
            self::STAGE_COLLECT_CATEGORY,
            self::STAGE_COLLECT_ITEM
        ];

        if ($this->getIsActiveCategoryImport()) {
            array_push($stages, self::STAGE_IMPORT_CATEGORY);
        }

        if ($this->getIsActiveProductImport()) {
            array_push($stages, self::STAGE_IMPORT_PRODUCT);
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
     * @return $this
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
    public function importCategories()
    {
        $this->addResponse(__('Category import is not implemented yet.'), Status::SUCCESS);

        return $this;

        if (!$this->getIsActiveCategoryImport()) {
            $this->addMessages(__('Category import is disabled.'));
            return $this;
        }

        $defaultLang = $this->getDefaultLang();
        $this->_categoryImportManagement->setProfile($this->getProfile())
            ->execute($defaultLang);

        if ($response = $this->_categoryImportManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Category import has been processed.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function importProducts()
    {
        if (!$this->getIsActiveProductImport()) {
            $this->addResponse(__('Skipped. Product import is inactive.'), Status::SUCCESS);
            return $this;
        }

        $this->_productImportManagement->setProfile($this->getProfile())
            ->setProfileEntity($this)
            ->setProfileHistory($this->getHistory())
            ->execute();

        if ($response = $this->_productImportManagement->getResponse()) {
            $this->setResponse($this->_productImportManagement->getResponse());
        } else {
            $this->addResponse(__('Product import has been processed.'), Status::SUCCESS);
        }


        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveProductImport()
    {
        if ($this->hasData(self::CONFIG_IS_ACTIVE_PRODUCT_IMPORT)) {
            return $this->getData(self::CONFIG_IS_ACTIVE_PRODUCT_IMPORT);
        }
        return $this->_getConfig(self::XML_PATH_ENABLE_PRODUCT_IMPORT);
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsActiveProductImport($bool)
    {
        $this->setData(self::CONFIG_IS_ACTIVE_PRODUCT_IMPORT, $bool);
        return $this;
    }

    /**
     * @return array|string|null
     */
    public function getImportBehaviour()
    {
        if ($this->hasData(self::CONFIG_IMPORT_BEHAVIOUR)) {
            return $this->getData(self::CONFIG_IMPORT_BEHAVIOUR);
        }
        return $this->_getConfig(self::XML_PATH_IMPORT_BEHAVIOUR);
    }

    /**
     * @return int
     */
    public function getImportBatchSize()
    {
        if ($this->hasData(self::CONFIG_IMPORT_BATCH_SIZE)) {
            return $this->getData(self::CONFIG_IMPORT_BATCH_SIZE);
        }
        return $this->_getConfig(self::XML_PATH_IMPORT_BATCH_SIZE);
    }

    /**
     * @return bool
     */
    public function getIsActiveReindexAfter()
    {
        if ($this->hasData(self::CONFIG_IS_ACTIVE_REINDEX_AFTER)) {
            return $this->getData(self::CONFIG_IS_ACTIVE_REINDEX_AFTER);
        }
        return $this->_getConfig(self::XML_PATH_REINDEX_AFTER);
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
        if ($this->hasData(self::CONFIG_API_BEHAVIOUR)) {
            return $this->getData(self::CONFIG_API_BEHAVIOUR);
        }
        return $this->_getConfig(self::XML_PATH_API_BEHAVIOUR);
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
        if ($this->hasData(self::CONFIG_API_COLLECTION_SIZE)) {
            return $this->getData(self::CONFIG_API_COLLECTION_SIZE);
        }
        return $this->_getConfig(self::XML_PATH_API_COLLECTION_SIZE);
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
        return $this->_getConfig(self::XML_PATH_API_ITEM_SEARCH_FILTERS);
    }

    /**
     * @return string|null
     */
    public function getApiVariationSearchFilters()
    {
        return $this->_getConfig(self::XML_PATH_API_VARIATION_SEARCH_FILTERS);
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
        if (!$values = $this->_getConfig(self::XML_PATH_STORE_MAPPING)) {
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
    public function getFlagOne() : ?int
    {
        if ($this->hasData(self::CONFIG_FLAG_ONE)) {
            return $this->getData(self::CONFIG_FLAG_ONE);
        }
        return $this->_getConfig(self::XML_PATH_FLAG_ONE);
    }

    /**
     * @return int
     */
    public function getFlagTwo() : ?int
    {
        if ($this->hasData(self::CONFIG_FLAG_TWO)) {
            return $this->getData(self::CONFIG_FLAG_TWO);
        }
        return $this->_getConfig(self::XML_PATH_FLAG_TWO);
    }

    /**
     * @param null $store
     * @return int
     */
    public function getDefaultTaxClass($store = null)
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_TAX_CLASS, $store);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getTaxMapping($store = null)
    {
        $taxMapping = [];
        if (!$values = $this->_getConfig(self::XML_PATH_TAX_MAPPING, $store)) {
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
     * @param null $store
     * @return array
     */
    public function getPriceMapping($store = null)
    {
        $priceMapping = [];
        if (!$values = $this->_getConfig(self::XML_PATH_PRICE_MAPPING, $store)) {
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
        return $this->_getConfig(self::XML_PATH_PURCHASE_PRICE_MAPPING);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIsActiveStockImport($storeId = null)
    {
        return $this->_getConfig(self::XML_PATH_IS_ACTIVE_STOCK_IMPORT, $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null)
    {
        return $this->_getConfig(self::XML_PATH_MAIN_WAREHOUSE_ID, $storeId);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getDefaultAttributeSet($store = null)
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_ATTRIBUTE_SET, $store);
    }

    /**
     * @return bool
     */
    public function getIsActiveAttributeRestriction()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_ATTRIBUTE_RESTRICTION);
    }

    /**
     * @return array
     */
    public function getAllowedAttributes()
    {
        if (!$this->getIsActiveAttributeRestriction()
            || !$excludedAttributes = $this->_getConfig(self::XML_PATH_ALLOWED_ATTRIBUTES)
        ) {
            return [];
        }

        return explode(',', $excludedAttributes);
    }

    /**
     * @return array
     */
    public function getNameMapping()
    {
        $names = [];
        if (!$values = $this->_getConfig(self::XML_PATH_PRODUCT_NAME_MAPPING)) {
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
        return $this->_getConfig(self::XML_PATH_SHORT_DESCRIPTION_MAPPING, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getDescriptionMapping($store = null)
    {
        return $this->_getConfig(self::XML_PATH_DESCRIPTION_MAPPING, $store);
    }

    /**
     * @return string
     */
    public function getTechnicalDataMapping()
    {
        return $this->_getConfig(self::XML_PATH_TECHNICAL_DATA_MAPPING);
    }

    /**
     * @return string
     */
    public function getSupplierNameMapping()
    {
        return $this->_getConfig(self::XML_PATH_SUPPLIER_NAME_MAPPING);
    }

    /**
     * @return string
     */
    public function getSupplierItemNumberMapping()
    {
        return $this->_getConfig(self::XML_PATH_SUPPLIER_NUMBER_MAPPING);
    }

    /**
     * @return string
     */
    public function getManufacturerMapping()
    {
        return $this->_getConfig(self::XML_PATH_MANUFACTURER_MAPPING);
    }

    /**
     * @return bool
     */
    public function getIsActiveImportUrl()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_IMPORT_URL);
    }

    /**
     * @return int
     */
    public function getImportUrlOption()
    {
        return $this->_getConfig(self::XML_PATH_IMPORT_URL_OPTIONS);
    }

    /**
     * @return string
     */
    public function getDefaultWeightUnit()
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_WEIGHT_UNIT);
    }

    /**
     * @return string|null
     */
    public function getItemWidthMapping()
    {
        /** @todo implement this */
    }

    /**
     * @return string|null
     */
    public function getItemLengthMapping()
    {
        /** @todo implement this */
    }

    /**
     * @return string|null
     */
    public function getItemHeightMapping()
    {
        /** @todo implement this */
    }


    /**
     * @return array
     */
    public function getPropertyMapping()
    {
        $properties = [];
        if (!$values = $this->_getConfig(self::XML_PATH_PROPERTY_MAPPING)) {
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
        if (!$values = $this->_getConfig(self::XML_PATH_BARCODE_MAPPING)) {
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
        if (!$values = $this->_getConfig(self::XML_PATH_MARKET_NUMBER_MAPPING)) {
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
    public function getIsActiveCategoryImport()
    {
        return $this->_getConfig(self::XML_PATH_IS_ACTIVE_CATEGORY_IMPORT);
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsActiveCategoryImport($bool)
    {
        $this->setData(self::CONFIG_IS_ACTIVE_CATEGORY_IMPORT, $bool);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getCategoryFallback()
    {
        return $this->_getConfig(self::XML_PATH_CATEGORY_FALLBACK);
    }

    /**
     * @return array
     */
    public function getRootCategoryMapping()
    {
        $rootCategories = [];
        if (!$values = $this->_getConfig(self::XML_PATH_ROOT_CATEGORY_MAPPING)) {
            return $rootCategories;
        }

        $rootCategories = $this->_serializer->unserialize($values);

        return $rootCategories;
    }

    /**
     * @param $mageCategoryId
     * @return int|null
     */
    public function getPlentyRootCategoryIdMapping($mageCategoryId)
    {
        if (!$categories = array_values($this->getRootCategoryMapping())) {
            return null;
        }

        $index = $this->getSearchArrayMatch($mageCategoryId, $categories, 'mage_category');
        if (false === $index || !isset($categories[$index]['plenty_category'])) {
            return null;
        }

        return $categories[$index]['plenty_category'];
    }

    /**
     * @param $plentyCategoryId
     * @return int|null
     */
    public function getMageRootCategoryIdMapping($plentyCategoryId)
    {
        if (!$categories = array_values($this->getRootCategoryMapping())) {
            return null;
        }

        $index = $this->getSearchArrayMatch($plentyCategoryId, $categories, 'plenty_category');
        if (false === $index || !isset($categories[$index]['mage_category'])) {
            return null;
        }

        return $categories[$index]['mage_category'];
    }

    /**
     * @return bool
     */
    public function getIsActiveMediaImport()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_MEDIA_IMPORT);
    }

    /**
     * @return bool
     */
    public function getIsActiveDownloadMedia()
    {
        return (bool) $this->_getConfig(self::XML_PATH_ENABLE_DOWNLOAD_MEDIA);
    }

    /**
     * @return string
     */
    public function getMediaFilter()
    {
        return $this->_getConfig(self::XML_PATH_MEDIA_FILTER);
    }

    /**
     * @return bool
     */
    public function getIsActiveCrossSellsImport()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_CROSSSELLS_IMPORT);
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
    public function getProductImportSearchCriteria()
    {
        return $this->getData(self::CONFIG_IMPORT_SEARCH_CRITERIA);
    }

    /**
     * @var array $filter
     *
     * Column-value pairs:
     * ['entity_id' => 100]
     * or multi-dimensional array of column-value pairs:
     * ['entity_id' => ['in' => [100, 101, 102]]
     *
     * @return $this
     */
    public function setProductImportSearchCriteria(array $filter)
    {
        $this->setData(self::CONFIG_IMPORT_SEARCH_CRITERIA, $filter);
        return $this;
    }

    /**
     * @return array
     */
    public function getProductImportSearchCriteriaAllowedFields()
    {
        return ['entity_id', 'item_id', 'variation_id', 'external_id'];
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
