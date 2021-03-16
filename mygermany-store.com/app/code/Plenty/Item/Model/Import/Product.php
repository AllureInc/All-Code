<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;

use Plenty\Core\Model\Profile\Type;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Collection;
use Plenty\Item\Model\ResourceModel\Import\Item\CollectionFactory;
use Plenty\Item\Profile\Config\Source;

use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Item\Api\ItemRepositoryInterface;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;
use Plenty\Core\Model\Profile\Type\AbstractType;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Plugin\ImportExport\Model\Import;
use Plenty\Core\Plugin\ImportExport\Model\ImportFactory;

/**
 * Class Product
 * @package Plenty\Item\Model\Import
 *
 * @method string getBehaviour()
 * @method Product setBehaviour(string $value)
 * @method ProductImportInterface getProfileEntity()
 * @method ProductImportInterface setProfileEntity(object $value)
 * @method HistoryInterface getProfileHistory()
 * @method HistoryInterface setProfileHistory(object $value)
 * @deprecated
 */
class Product extends ImportExportAbstract
{
    /**
     * @var array
     */
    private $_importFactory;

    /**
     * @var Import
     */
    private $_importModel;

    /**
     * @var CollectionFactory
     */
    private $_itemCollectionFactory;

    /**
     * @var Item\Variation
     */
    private $_variationFactory;

    /**
     * @var CategoryFactory
     */
    private $_categoryFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $_attributeOptionManagementInterface;

    /**
     * @var AttributeOptionInterface
     */
    private $_attributeOptionInterface;

    /**
     * Core data
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filter;

    /**
     * @var array
     */
    private $_importData;

    /**
     * @var array
     */
    private $_itemData;

    /**
     * @var array
     */
    private $_itemAddOnAttributes;

    /**
     * @var array
     */
    private $_propertyData;

    /**
     * @var array
     */
    private $_mediaData;

    /**
     * @var array
     */
    private $_messages;

    /**
     * @var array
     */
    private $_requiredAttributes   = [
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
     * @var array
     */
    private $_allowedAttributes = [];

    /**
     * Product constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param ImportFactory $importFactory
     * @param CollectionFactory $collectionFactory
     * @param FilterManager $filter
     * @param CategoryFactory $categoryFactory
     * @param AttributeOptionInterface $attributeOptionInterface
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagementInterface
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
        ImportFactory $importFactory,
        CollectionFactory $collectionFactory,
        FilterManager $filter,
        CategoryFactory $categoryFactory,
        AttributeOptionInterface $attributeOptionInterface,
        ProductAttributeOptionManagementInterface $attributeOptionManagementInterface,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_importFactory = $importFactory;
        $this->_itemCollectionFactory = $collectionFactory;
        $this->_filter = $filter;
        $this->_categoryFactory = $categoryFactory;
        $this->_attributeOptionInterface = $attributeOptionInterface;
        $this->_attributeOptionManagementInterface = $attributeOptionManagementInterface;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * Imports products
     *
     * @param null $itemId
     * @param null $variationId
     * @return $this
     * @throws \Exception
     */
    public function execute($itemId = null, $variationId = null)
    {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile is not set. [Trace: %1]', __METHOD__));
        }

        if (!$this->getProfileHistory()) {
            throw new \Exception(__('History is not set. [Trace: %1]', __METHOD__));
        }

        try {
            $this->_import($itemId, $variationId);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Executes import process
     *
     * @param null $itemId
     * @param null $variationId
     * @return $this
     * @throws \Exception
     */
    protected function _import($itemId = null, $variationId = null)
    {
        /** @var Collection $collection */
        $collection = $this->_itemCollectionFactory->create();
        $collection->addProfileFilter($this->getProfileEntity()->getProfile()->getId());

        if (null !== $itemId) {
            $collection->addItemFilter($itemId);
        } elseif (null !== $variationId) {
            $collection->addVariationFilter($variationId);
        } else {
            $collection->addPendingFilter();
        }

        if (!$collection->getSize()) {
            $this->_messages[] = __('No products are found for import.');
            return $this;
        }

        if ($flagOne = $this->getProfileEntity()->getFlagOne()) {
            $collection->addFlagFilter('flag_one', $flagOne);
        }
        if ($flagTwo = $this->getProfileEntity()->getFlagTwo()) {
            $collection->addFlagFilter('flag_two', $flagTwo);
        }

        $collection->setOrder('product_type', 'DESC');

        $batch = $this->getProfileEntity()->getImportBatchSize()
            ? $this->getProfileEntity()->getImportBatchSize()
            : 10;

        $collection->setPageSize((int) $batch);
        $lastPage = $collection->getLastPageNumber();
        $pageNumber = 1;
        do {
            $collection->setCurPage($pageNumber);
            $collection->load();
            
            $importData = $this->_buildImportData($collection);

            try {
                $this->__getImportModel()->setEntityCode('catalog_product');
                $this->__getImportModel()->processImport($importData);
            } catch (\Exception $e) {
                continue;
            }

            $collection->clear();
            $pageNumber++;
        } while ($pageNumber <= $lastPage);

        return $this;
    }

    /**
     * Builds import batch data
     *
     * @param Collection $collection
     * @return array
     */
    protected function _buildImportData(Collection $collection)
    {
        $this->__initImportData();
        
        /** @var \Plenty\Item\Model\Import\Item $item */
        foreach ($collection as $item) {
            if ($item->getProductType() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                /** @var \Plenty\Item\Api\Data\Import\Item\VariationInterface $child */
                foreach ($item->getItemLinkedVariations() as $child) {
                    try {
                        $this->__initVariation($item, $child->getVariationId());
                        $this->_getVariation()->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                        $this->_generate($item);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            try {
                $this->__initVariation($item);
                $this->_getVariation()->setProductType($item->getProductType());
                // $this->_getVariation()->setBundleType($item->getBundleType());
                $this->_generate($item);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->_importData;
    }

    /**
     * Generates item data
     *
     * @param Item $item
     * @return array
     */
    protected function _generate(Item $item)
    {
        $this->__initItemImportData();

        // handle multi-store attributes
        $stores = $this->getProfileEntity()->getStoreMapping();
        /** @var DataObject $store */
        foreach ($stores as $store) {
            $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
            $isDefaultStore = $store->getData(AbstractType::IS_DEFAULT_STORE);

            try {
                // Add main attributes
                $this->_addMainAttributes($item, $store);

                // Add multi-store data
                if (!$isDefaultStore) {
                    $this->_itemData[$mageStore]['sku'] = $this->_getVariation()->getSku();
                    $this->_itemData[$mageStore]['store_view_code'] = $mageStore;
                    // $this->_itemData[$mageStore]['product_websites'] = $store->getData(AbstractType::MAGE_WEBSITE_CODE);
                }

                // Add variation texts
                $this->_addTexts($store);

                // Add sales prices
                $this->_addSalesPrices($store);

                // Add variation attribute values
                $this->_addVariationAttributes($store);

                // Add variation barcodes
                $this->_addBarcodes($store);

                // Add categories
                $this->_addCategories($store);

                // Add properties (attributes)
                $this->_addProperties($store);

                // $this->_addMedia($item, $store);

                // Add suppliers
                $this->_addSuppliers($store);

                // Add supper attributes
                $this->_addConfigurableVariations($item, $store);
            } catch (\Exception $e) {
                continue;
            }
        }

        // Add attribute data
        foreach ($this->_itemData as $itemData) {
            $this->_importData[] = $itemData;
        }

        // Add category data
        /*
        foreach ($this->_categoryData as $categoryData) {
            $this->_importData[] = $categoryData;
        } */

        // Add property data
        foreach ($this->_propertyData as $propertyData) {
            $this->_importData[] = $propertyData;
        }

        // Add media data
        foreach ($this->_mediaData as $mediaData) {
            $this->_importData[] = $mediaData;
        }

        if (empty($this->_allowedAttributes)) {
            return $this->_importData;
        }

        // handle attribute restrictions
        foreach ($this->_importData as $index => $importData) {
            $allowedAttributes = array_merge($this->_requiredAttributes, $this->_allowedAttributes);
            $this->_importData[$index] = array_filter(
                $importData,
                function ($key) use ($allowedAttributes) {
                    return in_array($key, $allowedAttributes);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $this->_importData;

        /*
        $bundleComponents = $itemVariation->getVariationBundleComponents();
        $variationMarketNumbers = $itemVariation->getVariationMarketNumbers(); */
    }

    /**
     * @param Item $item
     * @param DataObject $store
     * @return array
     */
    protected function _addMainAttributes(Item $item, DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);

        $variationStock = $this->_getVariation()->getVariationStock();
        $this->_itemData[$mageStore] = [
            'profile_id' => $this->getProfileEntity()->getProfile()->getId(),
            'sku' => $this->_getVariation()->getSku(),
            'attribute_set_code' => $this->getProfileEntity()->getDefaultAttributeSet(),
            'product_type' => $this->_getVariation()->getProductType(),
            'status' => $this->_getVariation()->getIsActive()
                ? \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                : \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
            'visibility' => $this->_getVariation()->getIsMain()
                ? 'Catalog, Search'
                : 'Not Visible Individually',
            'product_websites' => $this->__getProductWebsites(),
            'plenty_item_id' => $this->_getVariation()->getItemId(),
            'plenty_variation_id' => $this->_getVariation()->getVariationId(),
        ];

        // handle weight
        if ($weight = $this->_getVariation()->getData($this->getProfileEntity()->getDefaultWeightUnit())) {
            $this->_itemData[$mageStore]['weight'] = $weight;
        }

        // handle tax class
        if (null !== $this->_getVariation()->getVatId()
            && $taxMapping = $this->getProfileEntity()->getTaxMapping()
        ) {
            $taxMapping = array_flip($taxMapping);
            if (isset($taxMapping[$this->_getVariation()->getVatId()])) {
                $this->_itemData[$mageStore]['tax_class_id'] = $taxMapping[$this->_getVariation()->getVatId()];
            }
        } else {
            $this->_itemData[$mageStore]['tax_class_id'] = $this->getProfileEntity()->getDefaultTaxClass();
        }

        // handle purchase price
        if ($purchasePriceMapping = $this->getProfileEntity()->getPurchasePriceMapping()) {
            $this->_itemData[$mageStore][$purchasePriceMapping] = $this->_getVariation()->getPurchasePrice()
                ? (float) $this->_getVariation()->getPurchasePrice()
                : 0.0000;
        }

        // handle stock
        if ($this->getProfileEntity()->getIsActiveStockImport()
            && $netStock = $variationStock->getNetStock()
        ) {
            $this->_itemData[$mageStore]['qty'] = $netStock;
            $this->_itemData[$mageStore]['is_in_stock'] = $netStock > 0;
        }

        // handle manufacturer
        if ($item->getManufacturerId()
            && $manufacturer = $item->getItemManufacturer($item->getManufacturerId())
        ) {
            $this->_itemData[$mageStore]['manufacturer'] = $manufacturer->getManufacturerName();
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return array
     */
    protected function _addTexts(DataObject $store)
    {
        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        $plentyStore = $store->getData(AbstractType::PLENTY_STORE);

        /** @var \Plenty\Item\Model\Import\Item\Texts $itemTexts */
        $itemTexts = $this->_getVariation()->getVariationTexts($plentyStore)
            ->getFirstItem();

        // handle names
        if (!$this->_getVariation()->getIsMain()) {
            $this->_itemData[$mageStore]['name'] = $this->_getVariation()->getName();
        } elseif ($nameMapping = $this->getProfileEntity()->getNameMapping()) {
            foreach ($nameMapping as $mageName => $plentyName) {
                if (!$name = $itemTexts->getData($plentyName)) {
                    continue;
                }
                $this->_itemData[$mageStore][$mageName] = $name;
            }
        } else {
            $this->_itemData[$mageStore]['name'] = $itemTexts->getName();
        }

        // handle description
        if ($description = $itemTexts->getDescription()) {
            $this->_itemData[$mageStore]['description'] = $description;
        }

        // handle short description
        if ($shortDescription = $itemTexts->getShortDescription()) {
            $this->_itemData[$mageStore]['short_description'] = $shortDescription;
        }

        // handle meta description
        if ($metaDescription = $itemTexts->getMetaDescription()) {
            $this->_itemData[$mageStore]['meta_description'] = $metaDescription;
        }

        // handle meta keywords
        if ($metaKeywords = $itemTexts->getMetaKeywords()) {
            $this->_itemData[$mageStore]['meta_keyword'] = $metaKeywords;
        }

        // handle technical data
        if ($itemTexts->getTechnicalData()
            && $technicalDataMapping = $this->getProfileEntity()->getTechnicalDataMapping()
        ) {
            $this->_itemData[$mageStore][$technicalDataMapping] = $itemTexts->getTechnicalData();
        }

        // handle url key
        if ($this->getProfileEntity()->getIsActiveImportUrl() && $this->_getVariation()->getIsMain()) {
            if (($this->getProfileEntity()->getImportUrlOption() === Source\Product\Attribute\Url\Options::USE_EXISTING)
                && $urlPath = $itemTexts->getUrlPath()
            ) {
                $this->_itemData[$mageStore]['url_key'] = $urlPath;
                $this->_itemData[$mageStore]['url_path'] = $urlPath;
            } else {
                $url = $this->__formatUrlKey($itemTexts->getName());
                $this->_itemData[$mageStore]['url_key'] = $url;
                $this->_itemData[$mageStore]['url_path'] = $url;
            }
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return mixed
     * @throws \Exception
     */
    protected function _addSalesPrices(DataObject $store)
    {
        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        if (!$priceMapping = $this->getProfileEntity()->getPriceMapping($mageStore)) {
            throw new \Exception(__('Failed to retrieve price mapping. [Trace: %1]', __METHOD__));
        }

        foreach ($priceMapping as $magePrice => $plentyPrice) {
            /** @var \Plenty\Item\Api\Data\Import\Item\SalesPriceInterface $salesPrice */
            $salesPrice = $this->_getVariation()->getVariationSalesPrices($plentyPrice)
                ->getFirstItem();
            if (!$price = $salesPrice->getPrice()) {
                continue;
            }

            $this->_itemData[$mageStore][$magePrice] = $price;
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return array
     */
    protected function _addVariationAttributes(DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$attributes = $this->_getVariation()->getVariationAttributeValues()
        ) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);

        $valuesUsedForNames = [];
        /** @var \Plenty\Item\Api\Data\Import\Item\AttributeValueInterface $attribute */
        foreach ($attributes as $attribute) {
            if (!$attributeCode = $attribute->getAttributeBackendName()) {
                continue;
            }

            if (!$attributeValue = $attribute->getValueBackendName()) {
                continue;
            }

            $this->_itemData[$mageStore][strtolower($attributeCode)] = $attributeValue;
            $valuesUsedForNames[] = $attributeValue;

            if (!$store->getData(AbstractType::IS_DEFAULT_STORE)) {
               continue;
            }

            $option = $this->__getAttributeOptionInterface();
            $option->setLabel($attributeValue);
            // $option->setStoreLabels([$mageStore => $attributeValue]);
            $option->setValue($attributeValue);
            $option->setSortOrder($attribute->getAttributePosition());
            $option->setIsDefault(0);
            try {
                $this->__getAttributeOptionManagementInterface()
                    ->add($attributeCode, $option);
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($this->_getVariation()->getIsMain()
            || empty($valuesUsedForNames)
            || !$nameMapping = $this->getProfileEntity()->getNameMapping()
        ) {
            return $this->_itemData;
        }

        foreach ($nameMapping as $mageName => $plentyName) {
            if (!isset($this->_itemData[$mageStore][$mageName])
                || !$name = $this->_itemData[$mageStore][$mageName]
            ) {
                continue;
            }

            $this->_itemData[$mageStore]['name'] = $name.' '.implode(' ', $valuesUsedForNames);
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return array
     */
    protected function _addBarcodes(DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$barCodeMapping = $this->getProfileEntity()->getBarcodeMapping()
        ) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);

        foreach ($barCodeMapping as $mageCode => $plentyId) {
            /** @var \Plenty\Item\Api\Data\Import\Item\BarcodeInterface $barCode */
            $barCode = $this->_getVariation()->getVariationBarcodes($plentyId)
                ->getFirstItem();
            if (!$code = $barCode->getCode()) {
                continue;
            }

            $this->_itemData[$mageStore][$mageCode] = $code;
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return array
     * @throws \Exception
     */
    protected function _addCategories(DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
        ) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        $variationCategories = $this->_getVariation()->getVariationCategories();
        $rootCategoryMapping = $this->getProfileEntity()->getRootCategoryMapping();
        if (!$variationCategories && !$rootCategoryMapping) {
            throw new \Exception(__('Can not find product categories. [Trace: %1]', __METHOD__));
        }

        if (!$variationCategories) {
            $this->_itemData[$mageStore]['categories'] = key($rootCategoryMapping);
            return $this->_itemData;
        }

        $categoryIds = $variationCategories->getColumnValues('category_id');
        $categoryModel = $this->_categoryFactory->create();
        $categoryImportRecords = $categoryModel->getCollection()
            ->addProfileFilter($this->getProfileEntity()->getProfile()->getId())
            ->addFieldToFilter('category_id', ['in' => $categoryIds]);

        if (!$categoryImportRecords->getSize()) {
            return [];
        }

        $categories = [];
        foreach ($rootCategoryMapping as $mageRootCategory => $plentyCategory) {
            /** @var \Plenty\Item\Api\Data\Import\CategoryInterface $category */
            foreach ($categoryImportRecords as $category) {
                $categoryPath = $category->getPath();
                if ($plentyCategory == $category->getCategoryId()) {
                    $categoryPath = $mageRootCategory;
                }

                if ($category->getLevel() > 1) {
                    $categoryPath = explode('/', $category->getPath());
                    array_shift($categoryPath);
                    array_unshift($categoryPath, $mageRootCategory);
                    $categoryPath = implode('/', $categoryPath);
                }

                if (!$categoryPath) {
                    continue;
                }

                $categories[] = $categoryPath;
            }
        }

        if (!empty($categories)) {
            $this->_itemData[$mageStore]['categories'] = implode(',', $categories);
        }

        return $this->_itemData;
    }

    /**
     * @param DataObject $store
     * @return array
     */
    protected function _addProperties(DataObject $store)
    {
        if (!$this->_getVariation()->getIsMain()) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        $plentyStore = $store->getData(AbstractType::PLENTY_STORE);

        $properties = $this->_getVariation()->getVariationProperties();
        /** @var \Plenty\Item\Api\Data\Import\Item\PropertyInterface $property */
        foreach ($properties as $property) {
            $propertyEntry = $property->getProperty();

            $attributeValue = null;
            $propertyBackendName = isset($propertyEntry['backendName']) ? $propertyEntry['backendName'] : null;

            $attributeCode = $propertyBackendName;
            if ($propertyMapping = $this->getProfileEntity()->getPropertyMapping()) {
                $propertyMapping = array_flip($propertyMapping);
                if (isset($propertyMapping[$property->getPropertyId()])) {
                    $attributeCode = $propertyMapping[$property->getPropertyId()];
                }
            }

            // Process properties with text fields
            if ($valueNames = $property->getNames()) {
                $namesIndex = $this->getSearchArrayMatch($plentyStore, $valueNames, 'lang');
                if (false !== $namesIndex) {
                    $attributeValue = isset($valueNames[$namesIndex]['value'])
                        ? $valueNames[$namesIndex]['value']
                        : null;
                }
            }

            // Process properties with type selection
            if ($store->getData(AbstractType::IS_DEFAULT_STORE)
                && $selections = $property->getPropertySelections()
            ) {
                $selectionIndex = $this->getSearchArrayMatch($plentyStore, $selections, 'lang');
                if (false !== $selectionIndex) {
                    $attributeValue = isset($selections[$selectionIndex]['name'])
                        ? $selections[$selectionIndex]['name']
                        : null;
                }
            }

            if (null === $attributeValue || $attributeValue === '') {
                continue;
            }

            // Add attribute option
            if ($store->getData(AbstractType::IS_DEFAULT_STORE) && $property->getPropertySelectionId()) {
                $option = $this->__getAttributeOptionInterface();
                $option->setLabel($attributeValue);
                // $option->setStoreLabels([$mageStore => $attributeValue]);
                $option->setValue($attributeValue);
                $option->setSortOrder(isset($propertyEntry['position']) ? $propertyEntry['position'] : 0);
                $option->setIsDefault(0);
                try {
                    $this->__getAttributeOptionManagementInterface()
                        ->add($attributeCode, $option);
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!isset($this->_itemData[$mageStore][strtolower($attributeCode)])) {
                $this->_itemData[$mageStore][strtolower($attributeCode)] = $attributeValue;
            } else {
                $this->_propertyData[] = [
                    strtolower($attributeCode) => $attributeValue
                ];
            }
        }

        return $this->_propertyData;
    }

    /**
     * @param Item $item
     * @param DataObject $store
     * @return array
     */
    protected function _addMedia(Item $item, DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || !$images = $item->getItemMediaImages()
        ) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        $plentyStore = $store->getData(AbstractType::PLENTY_STORE);


        $i = 1;
        /** @var \Plenty\Item\Api\Data\Import\Item\MediaInterface $image */
        foreach ($images as $image) {
            $namesEntry = $image->getNames();

            $mediaName = '';
            $index = $this->getSearchArrayMatch($plentyStore, $namesEntry, 'lang');
            if (false !== $index && isset($namesEntry[$index]['name'])) {
                $mediaName = $namesEntry[$index]['name'];
            }
            if ($i > 1) {
                $this->_mediaData[] = [
                    'additional_images'         => '/' . $image->getCleanImageName(),
                    'additional_images_labels'  => $mediaName,
                    'hide_from_product_page'    => false,
                ];
            } else {
                $this->_itemData[$mageStore]['base_image']              = '/' . $image->getCleanImageName();
                $this->_itemData[$mageStore]['base_image_label']        = $mediaName;
                $this->_itemData[$mageStore]['small_image']             = '/' . $image->getCleanImageName();
                $this->_itemData[$mageStore]['small_image_label']       = $mediaName;
                $this->_itemData[$mageStore]['thumbnail_image']         = '/' . $image->getCleanImageName();
                $this->_itemData[$mageStore]['thumbnail_image_label']   = $mediaName;
                $this->_itemData[$mageStore]['swatch_image']            = '/' . $image->getCleanImageName();
                $this->_itemData[$mageStore]['swatch_image_label']      = $mediaName;
            }
            $i++;
        }

        return $this->_mediaData;
    }

    /**
     * @param DataObject $store
     * @return array
     */
    protected function _addSuppliers(DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || !$supplierMapping = $this->getProfileEntity()->getSupplierNameMapping()
        ) {
            return [];
        }

        if (!$suppliers = $this->_getVariation()->getVariationSuppliers()) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);
        $supplierItemNumber = [];
        /** @var \Plenty\Item\Api\Data\Import\Item\SupplierInterface $supplier */
        foreach ($suppliers as $supplier) {

            // get supplier item number
            $supplierItemNumber[] = $supplier->getItemNumber()
                ? $supplier->getItemNumber()
                : null;

            if (!isset($this->_itemData[$mageStore][$supplierMapping])) {
                $this->_itemData[$mageStore][$supplierMapping] = $supplier->getSupplierId(); // @todo: provide supplier name
            } else {
                $this->_itemAddOnAttributes[][$supplierMapping] = $supplier->getSupplierId(); // @todo: provide supplier name
            }
        }

        if (empty($supplierItemNumber)
            || !$supplierItemNumberMapping = $this->getProfileEntity()->getSupplierItemNumberMapping()) {
            return $this->_itemData;
        }

        $this->_itemData[$mageStore][$supplierItemNumberMapping] = implode(', ', $supplierItemNumber);

        return  $this->_itemData;
    }


    /**
     * @param Item $item
     * @param DataObject $store
     * @return array
     */
    protected function _addConfigurableVariations(Item $item, DataObject $store)
    {
        if (!$store->getData(AbstractType::IS_DEFAULT_STORE)
            || !$this->_getVariation()->getIsMain()
            || ($this->_getVariation()->getProductType() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            || (!$children = $item->getItemLinkedVariations())
        ) {
            return [];
        }

        $mageStore = $store->getData(AbstractType::MAGE_STORE_CODE);

        /** @var \Plenty\Item\Api\Data\Import\Item\VariationInterface $child */
        foreach ($children as $child) {
            if (!$attributes = $child->getVariationAttributeValues()) {
                continue;
            }

            $variations = [];
            /** @var \Plenty\Item\Api\Data\Import\Item\AttributeValueInterface $attribute */
            foreach ($attributes as $attribute) {
                if (!$attributeCode = $attribute->getAttributeBackendName()) {
                    continue;
                }

                if (!$attributeValue = $attribute->getValueBackendName()) {
                    continue;
                }

                $this->_itemData[$mageStore]['configurable_variation_labels'] = $attributeCode;
                $variations[$attributeCode] = $attributeValue;
            }

            if (empty($variations)) {
                continue;
            }

            $variations = ['sku' => $child->getSku()] + $variations;
            $this->_itemData[$mageStore]['configurable_variations'][] = $variations;

        }

        return $this->_itemData;
    }

    /**
     * @return Item\Variation
     */
    protected function _getVariation()
    {
        return $this->_variationFactory;
    }

    /**
     * @param Item $item
     * @param null $variationId
     * @return Item\Variation
     * @throws \Exception
     */
    private function __initVariation(Item $item, $variationId = null)
    {
        if (!$this->_variationFactory = $item->getVariation($variationId)) {
            throw new \Exception(__('Cannot find item variation. [Item ID: %1, Variation ID: %2]',
                $item->getItemId(), $item->getVariationId()));
        }
        return $this->_getVariation();
    }

    /**
     * @param $str
     * @return string
     */
    private function __formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * @return Import
     */
    private function __getImportModel()
    {
        if (!$this->_importModel) {
            $this->_importModel = $this->_importFactory->create();
        }
        return $this->_importModel;
    }

    /**
     * @return ProductAttributeOptionManagementInterface
     */
    private function __getAttributeOptionManagementInterface()
    {
        return $this->_attributeOptionManagementInterface;
    }

    /**
     * @return AttributeOptionInterface
     */
    private function __getAttributeOptionInterface()
    {
        return $this->_attributeOptionInterface;
    }

    /**
     * @return array
     */
    private function __getProductWebsites()
    {
        return implode(',', $this->getProfileEntity()->getStoreMapping()
            ->getColumnValues(AbstractType::MAGE_WEBSITE_CODE));
    }

    /**
     * @return $this
     */
    private function __initImportData()
    {
        $this->_importData = [];
        $this->_allowedAttributes = $this->getProfileEntity()
            ->getAllowedAttributes();
        return $this;
    }

    /**
     * @return $this
     */
    private function __initItemImportData()
    {
        $this->_itemData =
        $this->_propertyData  =
        $this->_mediaData  = [];
        return $this;
    }
}