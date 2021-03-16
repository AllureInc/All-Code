<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Type as ProductType;

use Plenty\Core\Model\Source\Status;
use Plenty\Item\Api\ProductImportManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;
use Plenty\Item\Api\Data\Import\Item\SalesPriceInterface;
use Plenty\Item\Api\Data\Import\Item\AttributeValueInterface;
use Plenty\Item\Api\Data\Import\Item\BarcodeInterface;
use Plenty\Item\Api\Data\Import\Item\BundleInterface;
use Plenty\Item\Api\Data\Import\Item\PropertyInterface;
use Plenty\Item\Api\Data\Import\Item\MediaInterface;
use Plenty\Item\Api\Data\Import\Item\SupplierInterface;
use Plenty\Item\Api\Data\Import\Item\VariationInterface;
use Plenty\Item\Api\ItemRepositoryInterface;
use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Model\Import\Item;
use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\Import\Item\Texts as VariationTexts;
use Plenty\Item\Model\ResourceModel\Import\Item\Collection as ItemCollection;
use Plenty\Item\Model\ResourceModel\Import\Item\CollectionFactory as ItemCollectionFactory;
use Plenty\Item\Model\ResourceModel\Import\Category\CollectionFactory as CategoryCollectionFactory;
use Plenty\Item\Profile\Config\Source;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Plugin\ImportExport\Model\Import;
use Plenty\Core\Plugin\ImportExport\Model\ImportFactory;

/**
 * Class ProductImportManagement
 * @package Plenty\Item\Profile
 */
class ProductImportManagement extends AbstractManagement
    implements ProductImportManagementInterface
{
    const CONFIGURABLE_VARIATIONS = 'configurable_variations';
    const IS_CONFIGURABLE_VARIATION = 'is_configurable_variation';

    /**
     * @var Item
     */
    private $_item;

    /**
     * @var Variation
     */
    private $_variation;

    /**
     * @var CollectionFactory
     */
    private $_dataCollectionFactory;

    /**
     * @var Collection
     */
    private $_itemCollectionRequest;

    /**
     * @var DataObject
     */
    private $_itemRequest;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var array
     */
    private $_itemRequestData;

    /**
     * @var array
     */
    private $_attributeRequest;

    /**
     * @var array
     */
    private $_superAttributeRequest;

    /**
     * @var array
     */
    private $_bundlesRequest;

    /**
     * @var array
     */
    private $_mediaRequest;

    /**
     * @var DataObject
     */
    private $_currentStore;
    
    /**
     * @var null|string
     */
    private $_storeCode;

    /**
     * @var null|string
     */
    private $_storeLang;

    /**
     * @var array
     */
    private $_stores;

    /**
     * @var array
     */
    private $_initWebsites;

    /**
     * @var array
     */
    private $_initStores;

    /**
     * @var array
     */
    private $_storeViews;

    /**
     * @var null|WebsiteInterface
     */
    private $_defaultWebsite;

    /**
     * @var null|StoreInterface
     */
    private $_defaultStore;

    /**
     * @var array
     */
    private $_websiteCodeToId;

    /**
     * @var array
     */
    private $_websiteCodeToStoreIds;

    /**
     * @var CategoryInterface
     */
    private $_defaultRootCategory;

    /**
     * @var array
     */
    private $_allowedAttributes;

    /**
     * @var Import
     */
    private $_importModel;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $_categoryRepository;

    /**
     * @var ImportFactory
     */
    private $_importFactory;

    /**
     * @var ItemCollectionFactory
     */
    private $_itemCollectionFactory;

    /**
     * @var ItemRepositoryInterface
     */
    private $_itemRepository;

    /**
     * @var CategoryCollectionFactory
     */
    private $_categoryCollectionFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $_attributeOptionManagementInterface;

    /**
     * @var AttributeOptionInterface
     */
    private $_attributeOptionInterface;

    /**
     * @var FilterManager
     */
    private $_filter;

    /**
     * @var array
     */
    private $_requiredAttributes = [
        'profile_id',
        'sku',
        'name',
        'product_type',
        'attribute_set_code',
        'product_websites',
        'store_view_code',
        'visibility',
        'price',
        'tax_class_id',
        'qty',
        'is_in_stock',
        'categories',
        'configurable_variation_labels',
        'configurable_variations',
        'plenty_item_id',
        'plenty_variation_id'
    ];

    /**
     * ProductManagement constructor.
     * @param ImportFactory $importFactory
     * @param ItemRepositoryInterface $itemRepository
     * @param CollectionFactory $dataCollectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AttributeOptionInterface $attributeOptionInterface
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagementInterface
     * @param StoreManagerInterface $storeManager
     * @param FilterManager $filter
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ImportFactory $importFactory,
        ItemRepositoryInterface $itemRepository,
        CollectionFactory $dataCollectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        AttributeOptionInterface $attributeOptionInterface,
        ProductAttributeOptionManagementInterface $attributeOptionManagementInterface,
        StoreManagerInterface $storeManager,
        FilterManager $filter,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_importFactory = $importFactory;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_itemRepository = $itemRepository;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_attributeOptionInterface = $attributeOptionInterface;
        $this->_attributeOptionManagementInterface = $attributeOptionManagementInterface;
        $this->_storeManager = $storeManager;
        $this->_filter = $filter;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return ProductImportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): ProductImportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        return $this->_profileEntity;
    }

    /**
     * @param ProductImportInterface $profileEntity
     * @return $this|ProductImportManagementInterface
     */
    public function setProfileEntity(ProductImportInterface $profileEntity)
    {
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return HistoryInterface
     * @throws \Exception
     */
    public function getProfileHistory() : HistoryInterface
    {
        if (!$this->_profileHistory) {
            throw new \Exception(__('Profile history is not set.'));
        }

        return $this->_profileHistory;
    }

    /**
     * @param HistoryInterface $history
     * @return $this|mixed
     */
    public function setProfileHistory(HistoryInterface $history)
    {
        $this->_profileHistory = $history;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        /** @var ItemCollection $collection */
        $collection = $this->_itemCollectionFactory->create();
        $collection->addProfileFilter($this->getProfileEntity()->getProfile()->getId());

        if ($customFilter = $this->getProfileEntity()->getProductImportSearchCriteria()) {
            if (!in_array(key($customFilter), $this->getProfileEntity()->getProductImportSearchCriteriaAllowedFields())) {
                throw new \Exception(__('Field "%1" is not allowed when applying filter on product import collection. Available filter fields: %2',
                        key($customFilter),
                        implode(', ', $this->getProfileEntity()->getProductImportSearchCriteriaAllowedFields())
                    )
                );
            }

            if (!current($customFilter)) {
                throw new \Exception(__('Field value is required when applying filter on product import collection.'));
            }

            $collection->addFieldToFilter(key($customFilter), current($customFilter));
        } else {
            $collection->addPendingFilter();
        }

        if (!$collection->getSize()) {
            $this->setResponse(__('Products are up to date.'), 'success');
            return $this;
        }

        if ($flagOne = $this->getProfileEntity()->getFlagOne()) {
            $collection->addFlagFilter('flag_one', $flagOne);
        }
        if ($flagTwo = $this->getProfileEntity()->getFlagTwo()) {
            $collection->addFlagFilter('flag_two', $flagTwo);
        }

        $collectionSize = $this->getProfileEntity()->getImportBatchSize() ?? 100;

        $collection->setOrder('product_type', 'DESC')
            ->setPageSize($collectionSize);

        $this->_initWebsites()
            ->_initStores()
            ->_initDefaultRootCategory()
            ->_initImportData();

        $lastPage = $collection->getLastPageNumber();
        $pageNumber = 1;
        $importResult = [];

        do {
            $collection->setCurPage($pageNumber);
            $collection->load();

            try {
                $this->_buildRequest($collection);
                if (!$request = $this->_getRequest()) {
                    break;
                }

                if ($this->getProfileEntity()->getIsActiveRequestLog()) {
                    $this->_logger->debug('PRODUCT_IMPORT', $request);
                }

                $this->_importModel()
                    ->setProfile($this->getProfileEntity()->getProfile())
                    ->setEntityCode('catalog_product')
                    ->setBehavior($this->getProfileEntity()->getImportBehaviour())
                    ->setRequest($request)
                    ->execute();
                $importResult[Status::SUCCESS] = array_unique(array_column($request, 'plenty_variation_id'));
            } catch (\Exception $e) {
                $importResult[Status::ERROR] = $this->_importModel()->getErrors('variation_id')
                    ?? [$e->getMessage()];
            }

            $collection->clear();
            $pageNumber++;
        } while ($pageNumber <= $lastPage);

        foreach ($importResult as $key => $value) {
            $this->setResponse(
                __('Product import has been processed%1. Effected variation ID(s): %2',
                    $key !== Status::ERROR ?: ' with some errors', implode(', ', $value)
                ), $key
            );
        }

        return $this;
    }

    /**
     * @param ItemCollection $collection
     * @return array
     * @throws \Exception
     */
    protected function _buildRequest(ItemCollection $collection)
    {
        $this->_request = [];
        $this->_itemCollectionRequest = $this->_dataCollectionFactory->create();

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->_initItem($item);
            if (!$variation = $item->getVariation()) {
                continue;
            }

            if ($this->_getItem()->getProductType() === Configurable::TYPE_CODE) {
                /** @var Variation $child */
                foreach ($this->_getItem()->getUsedVariations() as $child) {
                    try {
                        $this->_initVariation($child);
                        $this->_itemRequest = new DataObject();
                        $child->setProductType(ProductType::TYPE_SIMPLE);
                        $child->setData(self::IS_CONFIGURABLE_VARIATION, true);
                        $this->_buildItemRequest();
                        $this->_getItemCollectionRequest()->addItem($this->_getItemRequest());
                    } catch (\Exception $e) {
                        $this->setResponse($e->getMessage(), 'error');
                    }
                }
            }

            try {
                $this->_initVariation($variation);
                $this->_getVariation()->setProductType($this->_getItem()->getProductType());
                $this->_getVariation()->setBundleType($this->_getItem()->getBundleType());
                $this->_itemRequest = new DataObject();
                $this->_buildItemRequest();
                $this->_getItemCollectionRequest()->addItem($this->_getItemRequest());
            } catch (\Exception $e) {
                $this->setResponse($e->getMessage(), 'error');
            }
        }

        $this->_buildImportArray();

        return $this->_getRequest();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function _buildItemRequest()
    {
        $this->_resetItemRequestData();
        $stores = $this->getProfileEntity()->getStoreMapping();

        if (!$variationSku = $this->_getVariation()->getSku()) {
            throw new \Exception(__('Variation has no SKU. [Variation: %1]', $this->_getVariation()->getVariationId()));
        }

        /** @var DataObject $store */
        foreach ($stores as $store) {
            $this->_setCurrentStore($store);
            $this->_setStoreCode($store->getData(ProductImportInterface::MAGE_STORE_CODE));
            $this->_setStoreLang($store->getData(ProductImportInterface::PLENTY_STORE));

            $this->_addMainAttributes()
                ->_addTexts()
                ->_addSalesPrices()
                ->_addVariationAttributes()
                ->_addBarCodes()
                ->_addCategories()
                ->_addCharacteristics()
                ->_addProperties()
                ->_addMedia()
                ->_addSuppliers()
                ->_addSuperAttributes()
                ->_addBundle();
        }

        $this->_getItemRequest()->setData('default_attributes', $this->_getItemRequestData());
        $this->_getItemRequest()->setData('media', $this->_getMediaRequest());
        $this->_getItemRequest()->setData('attributes', $this->_getAttributeRequest());
        $this->_getItemRequest()->setData('super_attributes', $this->_getSupperAttributeRequest());
        $this->_getItemRequest()->setData('bundles', $this->_getBundleRequest());

        return $this->_getItemRequestData();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addMainAttributes()
    {
        $this->_itemRequestData[$this->_getStoreCode()] = [
            'sku' => $this->_getVariation()->getSku(),
            'plenty_item_id' => $this->_getVariation()->getItemId(),
            'plenty_variation_id' => $this->_getVariation()->getVariationId(),
            'attribute_set_code' => $this->_getItem()->getAttributeSet()
                ? $this->_getItem()->getAttributeSet()
                : $this->getProfileEntity()->getDefaultAttributeSet(),
            'product_type' => $this->_getVariation()->getProductType()
                ? $this->_getVariation()->getProductType()
                : $this->_getItem()->getProductType(),
            'status' => $this->_getVariation()->getIsActive()
                ? ProductStatus::STATUS_ENABLED
                : ProductStatus::STATUS_DISABLED,
            'visibility' => $this->_getVariation()->getIsMain()
                ? 'catalog, search'
                : 'not visible individually'
        ];

        if ($this->_getVariation()->getProductType() !== Configurable::TYPE_CODE) {
            $this->_setItemRequestData('store_view_code', $this->_getStoreCode());
        }

        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)) {
            return $this;
        }

        $this->_setItemRequestData('profile_id', $this->getProfileEntity()->getProfile()->getId());
        $this->_setItemRequestData('product_websites', $this->_getProductWebsites());

        if ($weight = $this->_getVariation()->getData($this->getProfileEntity()->getDefaultWeightUnit())) {
            $this->_setItemRequestData('weight', $weight);
        }

        if (null !== $this->_getVariation()->getVatId()
            && $taxMapping = $this->getProfileEntity()->getTaxMapping()
        ) {
            $taxMapping = array_flip($taxMapping);
            if (isset($taxMapping[$this->_getVariation()->getVatId()])) {
                $this->_setItemRequestData('tax_class_id', $taxMapping[$this->_getVariation()->getVatId()]);
            }
        } else {
            $this->_setItemRequestData('tax_class_id', $this->getProfileEntity()->getDefaultTaxClass());
        }

        if ($purchasePriceMapping = $this->getProfileEntity()->getPurchasePriceMapping()) {
            $this->_setItemRequestData(
                $purchasePriceMapping,
                $this->_getVariation()->getPurchasePrice()
                    ? (float) $this->_getVariation()->getPurchasePrice()
                    : 0.0000
            );
        }

        $variationStock = $this->_getVariation()->getVariationStock();
        if ($this->getProfileEntity()->getIsActiveStockImport()
            && $netStock = $variationStock->getNetStock()
        ) {
            $this->_setItemRequestData('qty', $netStock);
            $this->_setItemRequestData('is_in_stock', $netStock > 0);
        }

        if ($this->_getItem()->getManufacturerId()
            && $manufacturer = $this->_getItem()->getItemManufacturer($this->_getItem()->getManufacturerId())
        ) {
            $this->_setItemRequestData('manufacturer', $manufacturer->getManufacturerName());
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addTexts()
    {
        /** @var VariationTexts $itemTexts */
        $itemTexts = $this->_getVariation()->getVariationTexts($this->_getStoreLang())
            ->getFirstItem();

        if ($this->_getVariation()->getData(self::IS_CONFIGURABLE_VARIATION)) {
            if ($this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)) {
                $this->_setItemRequestData('name', $this->_getVariation()->getName());
            }
        } elseif ($nameMapping = $this->getProfileEntity()->getNameMapping()) {
            foreach ($nameMapping as $mageName => $plentyName) {
                if (!$name = $itemTexts->getData($plentyName)) {
                    continue;
                }
                $this->_setItemRequestData($mageName, $name);
            }
        } else {
            $this->_setItemRequestData('name', $itemTexts->getName());
        }

        if ($description = $itemTexts->getDescription()) {
            $this->_setItemRequestData('description', $description);
        }

        if ($shortDescription = $itemTexts->getShortDescription()) {
            $this->_setItemRequestData('short_description', $shortDescription);
        }

        if ($metaDescription = $itemTexts->getMetaDescription()) {
            $this->_setItemRequestData('meta_description', $metaDescription);
        }

        if ($metaKeywords = $itemTexts->getMetaKeywords()) {
            $this->_setItemRequestData('meta_keyword', $metaKeywords);
        }

        if ($itemTexts->getTechnicalData()
            && $technicalDataMapping = $this->getProfileEntity()->getTechnicalDataMapping()
        ) {
            $this->_setItemRequestData($technicalDataMapping, $itemTexts->getTechnicalData());
        }

        if ($this->getProfileEntity()->getIsActiveImportUrl()
            && $this->_getVariation()->getIsMain()
        ) {
            if (($this->getProfileEntity()->getImportUrlOption() === Source\Product\Attribute\Url\Options::USE_EXISTING)
                && $urlPath = $itemTexts->getUrlPath()
            ) {
                $this->_setItemRequestData('url_key', $urlPath);
                $this->_setItemRequestData('url_path', $urlPath);
            } else {
                $url = $this->_formatUrlKey($itemTexts->getName());
                $this->_setItemRequestData('url_key', $url);
                $this->_setItemRequestData('url_path', $url);
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addSalesPrices()
    {
        if (!$priceMapping = $this->getProfileEntity()->getPriceMapping($this->_getStoreCode())) {
            throw new \Exception(__('Failed to retrieve price mapping. [Trace: %1]', __METHOD__));
        }

        foreach ($priceMapping as $magePrice => $plentyPrice) {
            /** @var SalesPriceInterface $salesPrice */
            $salesPrice = $this->_getVariation()->getVariationSalesPrices($plentyPrice)
                ->getFirstItem();
            if (!$price = $salesPrice->getPrice()) {
                continue;
            }

            $this->_setItemRequestData($magePrice, $price);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addVariationAttributes()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$attributes = $this->_getVariation()->getVariationAttributeValues()
        ) {
            return $this;
        }

        $valuesUsedForNames = [];
        /** @var AttributeValueInterface $attribute */
        foreach ($attributes as $attribute) {
            if (!$attributeCode = $attribute->getAttributeBackendName()) {
                continue;
            }

            if (!$attributeValue = $attribute->getValueBackendName()) {
                continue;
            }

            $this->_setItemRequestData(strtolower($attributeCode), $attributeValue);
            $valuesUsedForNames[] = $attributeValue;

            $option = $this->_getAttributeOptionInterface();
            $option->setLabel($attributeValue);
            // $option->setStoreLabels([$mageStore => $attributeValue]);
            $option->setValue($attributeValue);
            $option->setSortOrder($attribute->getAttributePosition());
            $option->setIsDefault(0);
            try {
                $this->_getAttributeOptionManagementInterface()
                    ->add($attributeCode, $option);
            } catch (\Exception $e) {
                $this->setResponse(__('Could not create attribute option value. Reason: %1', $e->getMessage()), 'error');
                continue;
            }
        }

        if ($this->_getVariation()->getIsMain()
            || empty($valuesUsedForNames)
            || !$nameMapping = $this->getProfileEntity()->getNameMapping()
        ) {
            return $this;
        }

        foreach ($nameMapping as $mageName => $plentyName) {
            if (!$name = $this->_getItemRequestData($mageName)) {
                continue;
            }

            $this->_setItemRequestData('name', $name.' '.implode(' ', $valuesUsedForNames));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addBarCodes()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$barCodeMapping = $this->getProfileEntity()->getBarcodeMapping()
        ) {
            return $this;
        }

        foreach ($barCodeMapping as $mageCode => $plentyId) {
            /** @var BarcodeInterface $barCode */
            $barCode = $this->_getVariation()->getVariationBarcodes($plentyId)
                ->getFirstItem();
            if (!$value = $barCode->getCode()) {
                continue;
            }

            $this->_setItemRequestData($mageCode, $value);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addCategories()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
        ) {
            return $this;
        }

        $rootCategoryMapping = $this->getProfileEntity()
            ->getRootCategoryMapping();

        if (!$this->getProfileEntity()->getIsActiveCategoryImport()
            || !$rootCategoryMapping
            || !$variationCategories = $this->_getVariation()->getVariationCategories()
        ) {
            $this->_setItemRequestData('categories', $this->_getDefaultRootCategory()->getName());
            return $this;
        }

        $categoryIds = $variationCategories->getColumnValues('category_id');
        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categoryImportRecords = $categoryCollection->addProfileFilter($this->getProfileEntity()->getProfile()->getId())
            ->addFieldToFilter('category_id', ['in' => $categoryIds]);

        if (!$categoryImportRecords->getSize()) {
            return $this;
        }

        $categories = [];
        foreach ($categoryCollection as $category) {
            if (!$categoryPath = $category->getPath()) {
                continue;
            }

            $categories[] = $categoryPath;
        }

        if (!empty($categories)) {
            $this->_setItemRequestData('categories', implode(',', $categories));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addCharacteristics()
    {
        if (!$this->_getVariation()->getIsMain()
            || !$properties = $this->_getVariation()->getVariationProperties()
        ) {
            return $this;
        }

        /** @var PropertyInterface $property */
        foreach ($properties as $property) {
            $attributeValue = null;
            $propertyEntry = $property->getProperty();
            $propertyBackendName = $propertyEntry['backendName'] ?? null;
            $attributeCode = $propertyBackendName;

            if ($property->getPropertySelectionId()
                && !$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            ) {
                continue;
            }

            if ($propertyMapping = $this->getProfileEntity()->getPropertyMapping()) {
                $propertyMapping = array_flip($propertyMapping);
                $attributeCode = $propertyMapping[$property->getPropertyId()] ?? $attributeCode;
            }

            // Process properties with text fields
            if ($valueNames = $property->getNames()) {
                $index = $this->getSearchArrayMatch($this->_getStoreLang(), $valueNames, 'lang');
                if (false !== $index && isset($valueNames[$index]['value'])) {
                    $attributeValue = $valueNames[$index]['value'];
                }
            }

            // Process properties with type selection
            if ($this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
                && $selections = $property->getPropertySelections()
            ) {
                $index = $this->getSearchArrayMatch($this->_getStoreLang(), $selections, 'lang');
                if (false !== $index && isset($selections[$index]['name'])) {
                    $attributeValue = $selections[$index]['name'];
                }
            }

            if (null === $attributeValue || strlen($attributeValue) < 1) {
                continue;
            }

            // Add attribute option
            if ($this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
                && $property->getPropertySelectionId()
            ) {
                $option = $this->_getAttributeOptionInterface();
                $option->setLabel($attributeValue);
                // $option->setStoreLabels([$mageStore => $attributeValue]);
                $option->setValue($attributeValue);
                $option->setSortOrder(isset($propertyEntry['position']) ? $propertyEntry['position'] : 0);
                $option->setIsDefault(0);
                try {
                    $this->_getAttributeOptionManagementInterface()
                        ->add($attributeCode, $option);
                } catch (\Exception $e) {
                    $this->setResponse(__('Could not create attribute option value. Reason: %1', $e->getMessage()), 'error');
                    continue;
                }
            }

            if ($this->_getItemRequestData(strtolower($attributeCode))) {
                continue;
            }

            $this->_setItemRequestData(strtolower($attributeCode), $attributeValue);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addProperties()
    {
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addMedia()
    {
        if (!$this->getProfileEntity()->getIsActiveMediaImport()
            || !$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || !$images = $this->_getItem()->getItemMediaImages()
        ) {
            return $this;
        }

        $result = [];
        $i = 1;
        /** @var MediaInterface $image */
        foreach ($images as $image) {
            $namesEntry = $image->getNames();
            $mediaName = '';

            $index = $this->getSearchArrayMatch($this->_getStoreLang(), $namesEntry, 'lang');
            if (false !== $index && isset($namesEntry[$index]['name'])) {
                $mediaName = $namesEntry[$index]['name'];
            }

            if ($i > 1) {
                $result['additional_images'][] = $image->getUrl();
                $result['additional_images_labels'][] = $mediaName;
                /*
                $this->_mediaRequest[] = [
                    // 'additional_images' => '/' . $image->getCleanImageName(),
                    'additional_images' => $image->getUrl(),
                    'additional_images_labels' => $mediaName,
                    'hide_from_product_page' => false,
                ]; */
            } else {
                $this->_setItemRequestData('image', $image->getUrl());
                $this->_setItemRequestData('image_label', $mediaName);
                $this->_setItemRequestData('small_image', $image->getUrl());
                $this->_setItemRequestData('small_image_label', $mediaName);
                $this->_setItemRequestData('thumbnail', $image->getUrl());
                $this->_setItemRequestData('thumbnail_label', $mediaName);
                $this->_setItemRequestData('swatch_image', $image->getUrl());
                $this->_setItemRequestData('swatch_image_label', $mediaName);
            }
            $i++;
        }

        if (!empty($result)) {
            $this->_setItemRequestData('additional_images', implode(',', current($result)));
            $this->_setItemRequestData('additional_images_labels', implode(',', end($result)));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addSuppliers()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || !$suppliers = $this->_getVariation()->getVariationSuppliers()
        ) {
            return $this;
        }

        if (!$supplierMapping = $this->getProfileEntity()->getSupplierNameMapping()) {
            return $this;
        }


        $supplierItemNumber = [];
        /** @var SupplierInterface $supplier */
        foreach ($suppliers as $supplier) {

            // get supplier item number
            $supplierItemNumber[] = $supplier->getItemNumber() ?? null;

            if (!$this->_getItemRequestData($supplierMapping)) {
                $this->_setItemRequestData($supplierMapping, $supplier->getSupplierId()); // @todo: provide supplier name
            } else {
                $this->_setAttributeRequest($supplierMapping, $supplier->getSupplierId()); // @todo: provide supplier name
            }
        }

        if (!empty($supplierItemNumber)
            && $supplierItemNumberMapping = $this->getProfileEntity()->getSupplierItemNumberMapping()) {
            $this->_setAttributeRequest($supplierItemNumberMapping, implode(', ', $supplierItemNumber));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addSuperAttributes()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || $this->_getVariation()->getProductType() !== Configurable::TYPE_CODE
            || !$linkedVariations = $this->_getItem()->getUsedVariations()
        ) {
            return $this;
        }

        $result = [];
        /** @var VariationInterface $variation */
        foreach ($linkedVariations as $variation) {
            if (!$attributes = $variation->getVariationAttributeValues()) {
                continue;
            }

            $variations =
            $variationLabels = [];
            /** @var AttributeValueInterface $attribute */
            foreach ($attributes as $attribute) {
                if (!$attributeCode = $attribute->getAttributeBackendName()) {
                    continue;
                }

                if (!$attributeValue = $attribute->getValueBackendName()) {
                    continue;
                }

                $variations[$attributeCode] = $attributeValue;
                $variationLabels[$attributeCode] = ucfirst(strtolower($attributeCode));
            }

            $result['variation'][] = ['sku' => $variation->getSku()] + $variations;
        }

        if (empty($result)) {
            return $this;
        }

        $this->_setItemRequestData('configurable_variations', current($result));
        $this->_setItemRequestData('configurable_variation_labels', end($result));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _addBundle()
    {
        if (!$this->_getCurrentStore()->getData(ProductImportInterface::IS_DEFAULT_STORE)
            || $this->_getItem()->getProductType() !== ProductType::TYPE_BUNDLE
            || !$bundleComponents = $this->_getVariation()->getVariationBundleComponents()
        ) {
            return $this;
        }

        $this->_setItemRequestData('bundle_price_type', 'dynamic');
        $this->_setItemRequestData('bundle_sku_type', 'dynamic');
        $this->_setItemRequestData('bundle_price_view', 'price range');
        $this->_setItemRequestData('bundle_weight_type', 'dynamic');
        $this->_setItemRequestData('bundle_shipment_type', 'together');


        $bundleValues = [];
        $i = 0;
        /** @var BundleInterface $component */
        foreach ($bundleComponents as $component) {
            $bundleValues[] = [
                'name' => $component->getComponentName(),
                'type' => 'select',
                'required' => true,
                'sku' => $component->getComponentSku(),
                'price' => 0.00,
                'default' => $i < 1,
                'default_qty' => $component->getComponentQty(),
                'price_type' => 'fixed',
                'can_change_qty' => false
            ];
        }

        $this->_setItemRequestData('bundle_values', $bundleValues);

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function _buildImportArray()
    {
        /** @var DataObject $item */
        foreach ($this->_getItemCollectionRequest() as $item) {

            // attach main attributes
            foreach ($item->getData('default_attributes') as $defaultAttribute) {
                if (empty($this->_allowedAttributes)) {
                    $this->_request[] = $defaultAttribute;
                    continue;
                }

                $allowedAttributes = array_merge($this->_requiredAttributes, $this->_allowedAttributes);
                $finalAttributes = array_filter(
                    $defaultAttribute,
                    function ($key) use ($allowedAttributes) {
                        return in_array($key, $allowedAttributes);
                    },
                    ARRAY_FILTER_USE_KEY
                );

                if (!array_diff(array_keys($finalAttributes), $this->_requiredAttributes)) {
                    continue;
                }

                $this->_request[] = $finalAttributes;
            }

            // attach media
            if ($images = $item->getData('media')) {
                foreach ($images as $image) {
                    $this->_request[] = $image;
                }
            }
            // attach attributes
            if ($attributes = $item->getData('attributes')) {
                $i = 1;
                $resultAttributes = [];
                foreach ($attributes as $attribute) {
                    if (isset($resultAttributes[$i][key($attribute)])) {
                        $i++;
                    }
                    $resultAttributes[$i][key($attribute)] = current($attribute);
                }

                foreach ($resultAttributes as $attribute) {
                    $this->_request[] = $attribute;
                }
            }

            // attach super-attributes
            if ($superAttributes = $item->getData('super_attributes')) {
                foreach ($superAttributes as $superAttribute) {
                    $this->_request[] = $superAttribute;
                }
            }

            // attach bundles
            if ($bundles = $item->getData('bundles')) {
                foreach ($bundles as $bundle) {
                    $this->_request[] = $bundle;
                }
            }
        }

        return $this->_getRequest();
    }

    /**
     * @param $str
     * @return string
     */
    private function _formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * @return Import
     */
    private function _importModel()
    {
        if (!$this->_importModel) {
            $this->_importModel = $this->_importFactory->create();
        }
        return $this->_importModel;
    }

    /**
     * @return ProductAttributeOptionManagementInterface
     */
    private function _getAttributeOptionManagementInterface()
    {
        return $this->_attributeOptionManagementInterface;
    }

    /**
     * @return AttributeOptionInterface
     */
    private function _getAttributeOptionInterface()
    {
        return $this->_attributeOptionInterface;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function _getProductWebsites()
    {
        return implode(',', $this->getProfileEntity()->getStoreMapping()
            ->getColumnValues(ProductImportInterface::MAGE_WEBSITE_CODE));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _initImportData()
    {
        $this->_allowedAttributes = $this->getProfileEntity()
            ->getAllowedAttributes();
        return $this;
    }


    /**
     * @return $this
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    private function _initWebsites()
    {
        $defaultStoreMapping = $this->getProfileEntity()
            ->getDefaultStoreMapping();

        if (null === $this->_initWebsites) {
            $this->_initWebsites = $this->_storeManager->getWebsites();

            /** @var WebsiteInterface $website */
            foreach ($this->_initWebsites as $website) {
                $this->_websiteCodeToId[$website->getCode()] = $website->getId();
                $this->_websiteCodeToStoreIds[$website->getCode()] = array_flip($website->getStoreCodes());
                if (!$website->getIsDefault() || $defaultStoreMapping->hasData()) {
                    continue;
                }

                $this->_defaultWebsite = $website;
                $this->_defaultStore = $website->getDefaultStore();
            }
        }

        if (!$this->_defaultStore) {
            $this->_defaultStore = $this->_storeManager
                ->getStore($defaultStoreMapping->getData(ProductImportInterface::MAGE_STORE_ID));
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _initStores()
    {
        if (null === $this->_initStores) {
            $this->_initStores = $this->_storeManager->getStores();
            foreach ($this->_initStores as $store) {
                $this->_storeViews[$store->getId()] = $store->getCode();
                $this->_stores[] = $store->getCode();
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _initDefaultRootCategory()
    {
        if (!$this->getProfileEntity()->getIsActiveCategoryImport()
            && $fallbackCategory = $this->getProfileEntity()->getCategoryFallback()
        ) {
            $defaultCategoryId = $fallbackCategory;
        } else {
            $defaultCategoryId = $this->_getDefaultStore()->getRootCategoryId();
        }

        $this->_defaultRootCategory = $this->_categoryRepository->get($defaultCategoryId);

        return $this;
    }

    /**
     * @return CategoryInterface
     * @throws \Exception
     */
    private function _getDefaultRootCategory()
    {
        if (!$this->_defaultRootCategory) {
            throw new \Exception(__('Default category is not set.'));
        }

        return $this->_defaultRootCategory;
    }

    /**
     * @return Collection
     */
    private function _getItemCollectionRequest()
    {
        return $this->_itemCollectionRequest;
    }

    /**
     * @return DataObject
     */
    private function _getItemRequest()
    {
        return $this->_itemRequest;
    }

    /**
     * @return array
     */
    private function _getRequest()
    {
        return $this->_request;
    }

    /**
     * @param Item $item
     * @return $this
     */
    private function _initItem(Item $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * @param Variation $variation
     * @return $this
     */
    private function _initVariation(Variation $variation)
    {
        $this->_variation = $variation;
        return $this;
    }

    /**
     * @return Item
     * @throws \Exception
     */
    private function _getItem()
    {
        if (!$this->_item) {
            throw new \Exception(__('Item is not set.'));
        }

        return $this->_item;
    }

    /**
     * @return Variation
     * @throws \Exception
     */
    private function _getVariation()
    {
        if (!$this->_variation) {
            throw new \Exception(__('Variation is not set.'));
        }

        return $this->_variation;
    }

    /**
     * @return $this
     */
    private function _resetItemRequestData()
    {
        $this->_storeCode =
        $this->_itemRequestData =
        $this->_attributeRequest =
        $this->_superAttributeRequest =
        $this->_mediaRequest =
        $this->_bundlesRequest = null;
        return $this;
    }

    /**
     * @param null $value
     * @return array
     * @throws \Exception
     */
    private function _getItemRequestData($value = null)
    {
        if (!$value) {
            return $this->_itemRequestData;
        }

        return $this->_itemRequestData[$this->_getStoreCode()][$value] ?? [];
    }

    /**
     * @param string $key
     * @param string|array $value
     * @return $this
     * @throws \Exception
     */
    private function _setItemRequestData($key, $value)
    {
        $this->_itemRequestData[$this->_getStoreCode()][$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    private function _getMediaRequest()
    {
        return $this->_mediaRequest;
    }

    /**
     * @return array
     */
    private function _getAttributeRequest()
    {
        return $this->_attributeRequest;
    }

    /**
     * @param string|null $key
     * @param string|null $value
     * @return $this
     */
    private function _setAttributeRequest($key, $value)
    {
        $this->_attributeRequest[][$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    private function _getSupperAttributeRequest()
    {
        return $this->_superAttributeRequest;
    }

    /**
     * @return array
     */
    private function _getBundleRequest()
    {
        return $this->_bundlesRequest;
    }

    /**
     * @return StoreInterface|null
     * @throws \Exception
     */
    private function _getDefaultStore()
    {
        if (!$this->_defaultStore) {
            throw new \Exception(__('Default store is not set.'));
        }
        return $this->_defaultStore;
    }

    /**
     * @return DataObject
     * @throws \Exception
     */
    private function _getCurrentStore()
    {
        if (!$this->_currentStore) {
            throw new \Exception(__('Current store is not set.'));
        }
        return $this->_currentStore;
    }

    /**
     * @param DataObject $store
     * @return $this
     */
    private function _setCurrentStore(DataObject $store)
    {
        $this->_currentStore = $store;
        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    private function _getStoreCode()
    {
        if (!$this->_storeCode) {
            throw new \Exception(__('Store code is not set.'));
        }
        return $this->_storeCode;
    }

    /**
     * @param string $storeCode
     * @return $this
     */
    private function _setStoreCode($storeCode)
    {
        $this->_storeCode = $storeCode;
        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    private function _getStoreLang()
    {
        if (!$this->_storeLang) {
            throw new \Exception(__('Store language is not set.'));
        }
        return $this->_storeLang;
    }

    /**
     * @param string $storeLang
     * @return $this
     */
    private function _setStoreLang(string $storeLang)
    {
        $this->_storeLang = $storeLang;
        return $this;
    }
}