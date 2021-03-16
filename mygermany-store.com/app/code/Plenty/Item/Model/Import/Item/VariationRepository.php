<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

use Plenty\Item\Api\VariationRepositoryInterface;
use Plenty\Item\Api\Data\Import\VariationSearchResultsInterface;
use Plenty\Item\Api\Data\Import\VariationSearchResultsInterfaceFactory;
use Plenty\Item\Model\ResourceModel\Import\Item as ResourceModel;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Core\Model\Source\Status;
// Item / Variation Model interface
use Plenty\Item\Api\Data\Import\Item\VariationInterface;
use Plenty\Item\Api\Data\Import\Item\PropertyInterface;
use Plenty\Item\Api\Data\Import\Item\BarcodeInterface;
use Plenty\Item\Api\Data\Import\Item\BundleInterface;
use Plenty\Item\Api\Data\Import\Item\SalesPriceInterface;
use Plenty\Item\Api\Data\Import\Item\MarketNumberInterface;
use Plenty\Item\Api\Data\Import\Item\CategoryInterface;
use Plenty\Item\Api\Data\Import\Item\SupplierInterface;
use Plenty\Item\Api\Data\Import\Item\WarehouseInterface;
use Plenty\Item\Api\Data\Import\Item\MediaInterface;
use Plenty\Item\Api\Data\Import\Item\AttributeValueInterface;
use Plenty\Item\Api\Data\Import\Item\TextsInterface;
use Plenty\Item\Api\Data\Import\Item\StockInterface;
// Item Client interface
use Plenty\Item\Rest\Response\Item\PropertyDataInterface;
use Plenty\Item\Rest\Response\Item\BarcodeDataInterface;
use Plenty\Item\Rest\Response\Item\BundleDataInterface;
use Plenty\Item\Rest\Response\Item\SalesPriceDataInterface;
use Plenty\Item\Rest\Response\Item\MarketNumberDataInterface;
use Plenty\Item\Rest\Response\Item\CategoryDataInterface;
use Plenty\Item\Rest\Response\Item\SupplierDataInterface;
use Plenty\Item\Rest\Response\Item\WarehouseDataInterface;
use Plenty\Item\Rest\Response\Item\MediaDataInterface;
use Plenty\Item\Rest\Response\Item\AttributeValueDataInterface;
use Plenty\Item\Rest\Response\Item\TextDataInterface;
use Plenty\Item\Rest\Response\Item\StockDataInterface;

/**
 * Class VariationRepository
 * @package Plenty\Item\Model\Import\Item
 */
class VariationRepository implements VariationRepositoryInterface
{
    /**
     * @var ResourceModel\Variation
     */
    private $_resource;

    /**
     * @var ResourceModel\AttributeValue
     */
    private $_attributeValueResource;

    /**
     * @var ResourceModel\Barcode
     */
    private $_barCodeResource;

    /**
     * @var ResourceModel\Bundle
     */
    private $_bundleResource;

    /**
     * @var ResourceModel\Category
     */
    private $_categoryResource;

    /**
     * @var ResourceModel\MarketNumber
     */
    private $_marketNumberResource;

    /**
     * @var ResourceModel\Media
     */
    private $_mediaResource;

    /**
     * @var ResourceModel\Property
     */
    private $_propertyResource;

    /**
     * @var ResourceModel\SalesPrice
     */
    private $_salesPriceResource;

    /**
     * @var ResourceModel\Stock
     */
    private $_stockResource;

    /**
     * @var ResourceModel\Supplier
     */
    private $_supplierResource;

    /**
     * @var ResourceModel\Texts
     */
    private $_textsResource;

    /**
     * @var ResourceModel\Warehouse
     */
    private $_warehouseResource;

    /**
     * @var VariationFactory
     */
    private $_variationFactory;

    /**
     * @var ResourceModel\Variation\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var VariationSearchResultsInterfaceFactory
     */
    private $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var Json
     */
    private $_dateTime;

    private $_serializer;

    /**
     * @var array
     */
    private $_variations;

    /**
     * @var array
     */
    private $_properties;

    /**
     * @var array
     */
    private $_variationProperties;

    /**
     * @var array
     */
    private $_variationBarCodes;

    /**
     * @var array
     */
    private $_variationBundleComponents;

    /**
     * @var array
     */
    private $_variationSalesPrices;

    /**
     * @var array
     */
    private $_marketItemNumbers;

    /**
     * @var array
     */
    private $_variationCategories;

    /**
     * @var array
     */
    private $_variationSuppliers;

    /**
     * @var array
     */
    private $_variationWarehouses;

    /**
     * @var array
     */
    private $_variationMedia;

    /**
     * @var array
     */
    private $_variationAttributeValues;

    /**
     * @var array
     */
    private $_variationTexts;

    /**
     * @todo when ROUTE becomes available
     * @var array
     */
    private $_variationDescription;

    /**
     * @var array
     */
    private $_variationStock;

    /**
     * VariationRepository constructor.
     * @param ResourceModel\Variation $resource
     * @param ResourceModel\AttributeValue $attributeValueResource
     * @param ResourceModel\Barcode $barCodeResource
     * @param ResourceModel\Bundle $bundleResource
     * @param ResourceModel\Category $categoryResource
     * @param ResourceModel\MarketNumber $marketNumberResource
     * @param ResourceModel\Media $mediaResource
     * @param ResourceModel\Property $propertyResource
     * @param ResourceModel\SalesPrice $salesPriceResource
     * @param ResourceModel\Stock $stockResource
     * @param ResourceModel\Supplier $supplierResource
     * @param ResourceModel\Texts $textsResource
     * @param ResourceModel\Warehouse $warehouseResource
     * @param VariationFactory $variatinFactory
     * @param ResourceModel\Variation\CollectionFactory $collectionFactory
     * @param VariationSearchResultsInterfaceFactory $searchResultsFactory
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Variation $resource,
        ResourceModel\AttributeValue $attributeValueResource,
        ResourceModel\Barcode $barCodeResource,
        ResourceModel\Bundle $bundleResource,
        ResourceModel\Category $categoryResource,
        ResourceModel\MarketNumber $marketNumberResource,
        ResourceModel\Media $mediaResource,
        ResourceModel\Property $propertyResource,
        ResourceModel\SalesPrice $salesPriceResource,
        ResourceModel\Stock $stockResource,
        ResourceModel\Supplier $supplierResource,
        ResourceModel\Texts $textsResource,
        ResourceModel\Warehouse $warehouseResource,
        VariationFactory $variatinFactory,
        ResourceModel\Variation\CollectionFactory $collectionFactory,
        VariationSearchResultsInterfaceFactory $searchResultsFactory,
        Helper $helper,
        DateTime $dateTime,
        ?Json $serializer = null,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_attributeValueResource = $attributeValueResource;
        $this->_barCodeResource = $barCodeResource;
        $this->_bundleResource = $bundleResource;
        $this->_categoryResource = $categoryResource;
        $this->_marketNumberResource = $marketNumberResource;
        $this->_mediaResource = $mediaResource;
        $this->_propertyResource = $propertyResource;
        $this->_salesPriceResource = $salesPriceResource;
        $this->_stockResource = $stockResource;
        $this->_supplierResource = $supplierResource;
        $this->_textsResource = $textsResource;
        $this->_warehouseResource = $warehouseResource;
        $this->_variationFactory = $variatinFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VariationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var VariationSearchResultsInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param $entityId
     * @param null $field
     * @return VariationInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var VariationInterface $variation */
        $variation = $this->_variationFactory->create();
        $this->_resource->load($variation, $entityId, $field);
        if (!$variation->getId()) {
            throw new NoSuchEntityException(__('The variation with ID "%1" doesn\'t exist.', $entityId));
        }
        return $variation;
    }

    /**
     * @param $variationId
     * @return VariationInterface
     * @throws NoSuchEntityException
     */
    public function getById($variationId)
    {
        return $this->get($variationId, 'variation_id');
    }

    /**
     * @param $sku
     * @return VariationInterface
     * @throws NoSuchEntityException
     */
    public function getBySku($sku)
    {
        return $this->get($sku, 'sku');
    }

    /**
     * @param VariationInterface $variation
     * @return VariationInterface
     * @throws CouldNotSaveException
     */
    public function save(VariationInterface $variation)
    {
        try {
            $this->_resource->save($variation);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $variation;
    }

    /**
     * @param VariationInterface $variation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(VariationInterface $variation)
    {
        try {
            $this->_resource->delete($variation);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt()
    {
        $updatedBetween = null;

        /** @var ResourceModel\Variation\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToSelect('collected_at')
            ->setOrder('collected_at', 'desc')
            ->setPageSize(1);

        /** @var VariationInterface $variation */
        $variation = $collection->getFirstItem();

        if (strtotime($variation->getCollectedAt()) > 0) {
            $updatedBetween = $this->_helper->getDateTimeLocale($variation->getCollectedAt());
        }

        return $updatedBetween;
    }

    /**
     * @param Collection $responseData
     * @return $this|mixed
     * @throws \Exception
     */
    public function saveVariationCollection(Collection $responseData)
    {
        $this->_resetDataContainers();

        /** @var VariationInterface $item */
        foreach ($responseData as $item) {
            $itemId = $item->getItemId();
            $variationId = $item->getVariationId();
            $sku = $item->getSku();
            $externalId = $item->getExternalId();
            $mainVariationIndex = null;

            // HANDLE VARIATION DATA
            $this->_variations[] = $this->_prepareVariationData($item);

            // HANDLE PROPERTIES DATA
            if ($variationProperties = $item->getData(VariationInterface::VARIATION_PROPERTIES)) {
                foreach ($variationProperties as $variationProperty) {
                    $variationProperty[VariationInterface::ITEM_ID] = $itemId;
                    $variationProperty[VariationInterface::VARIATION_ID] = $variationId;
                    $variationProperty[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationProperty[VariationInterface::SKU] = $sku;
                    $this->_variationProperties[] = $this->_prepareVariationPropertiesData($variationProperty);
                }
            }

            // HANDLE BARCODE DATA
            if ($variationBarcodes = $item->getData(VariationInterface::VARIATION_BARCODES)) {
                foreach ($variationBarcodes as $variationBarcode) {
                    $variationBarcode[VariationInterface::ITEM_ID] = $itemId;
                    $variationBarcode[VariationInterface::VARIATION_ID] = $variationId;
                    $variationBarcode[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationBarcode[VariationInterface::SKU] = $sku;
                    $this->_variationBarCodes[] = $this->_prepareVariationBarcodeData($variationBarcode);
                }
            }

            // HANDLE BUNDLE COMPONENT DATA
            if ($variationBundleComponents = $item->getData(VariationInterface::VARIATION_BUNDLE_COMPONENTS)) {
                foreach ($variationBundleComponents as $variationBundleComponent) {
                    $variationBundleComponent[VariationInterface::ITEM_ID] = $itemId;
                    $variationBundleComponent[VariationInterface::VARIATION_ID] = $variationId;
                    $variationBundleComponent[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationBundleComponent[VariationInterface::SKU] = $sku;
                    $this->_variationBundleComponents['add'][] = $this->_prepareVariationBundleComponentData($variationBundleComponent);
                }
            } else {
                $this->_variationBundleComponents['remove'][] = $itemId;
            }

            // HANDLE SALES PRICE DATA
            if ($variationSalesPrices = $item->getData(VariationInterface::VARIATION_SALES_PRICES)) {
                foreach ($variationSalesPrices as $variationSalesPrice) {
                    $variationSalesPrice[VariationInterface::ITEM_ID] = $itemId;
                    $variationSalesPrice[VariationInterface::VARIATION_ID] = $variationId;
                    $variationSalesPrice[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationSalesPrice[VariationInterface::SKU] = $sku;
                    $this->_variationSalesPrices['add'][] = $this->_prepareVariationSalesPriceData($variationSalesPrice);
                }
            } else {
                $this->_variationSalesPrices['remove'][] = $variationId;
            }

            // HANDLE MARKET ITEM NUMBER DATA
            if ($marketItemNumbers = $item->getData(VariationInterface::MARKET_ITEM_NUMBERS)) {
                foreach ($marketItemNumbers as $marketItemNumber) {
                    $marketItemNumber[VariationInterface::ITEM_ID] = $itemId;
                    $marketItemNumber[VariationInterface::VARIATION_ID] = $variationId;
                    $marketItemNumber[VariationInterface::EXTERNAL_ID] = $externalId;
                    $marketItemNumber[VariationInterface::SKU] = $sku;
                    $this->_marketItemNumbers[] = $this->_prepareMarketItemNumberData($marketItemNumber);
                }
            }

            // HANDLE CATEGORY DATA
            if ($variationCategories = $item->getData(VariationInterface::VARIATION_CATEGORIES)) {
                foreach ($variationCategories as $variationCategory) {
                    $variationCategory[VariationInterface::ITEM_ID] = $itemId;
                    $variationCategory[VariationInterface::VARIATION_ID] = $variationId;
                    $variationCategory[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationCategory[VariationInterface::SKU] = $sku;
                    $this->_variationCategories[] = $this->_prepareVariationCategoryData($variationCategory);
                }
            }

            // HANDLE SUPPLIER DATA
            if ($variationSuppliers = $item->getData(VariationInterface::VARIATION_SUPPLIERS)) {
                foreach ($variationSuppliers as $variationSupplier) {
                    $variationSupplier[VariationInterface::ITEM_ID] = $itemId;
                    $variationSupplier[VariationInterface::VARIATION_ID] = $variationId;
                    $variationSupplier[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationSupplier[VariationInterface::SKU] = $sku;
                    $this->_variationSuppliers[] = $this->_prepareVariationSupplierData($variationSupplier);
                }
            }

            // HANDLE WAREHOUSE DATA
            if ($variationWarehouses = $item->getData(VariationInterface::VARIATION_WAREHOUSES)) {
                foreach ($variationWarehouses as $variationWarehouse) {
                    $variationWarehouse[VariationInterface::ITEM_ID] = $itemId;
                    $variationWarehouse[VariationInterface::VARIATION_ID] = $variationId;
                    $variationWarehouse[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationWarehouse[VariationInterface::SKU] = $sku;
                    $this->_variationWarehouses[] = $this->_prepareVariationWarehouseData($variationWarehouse);
                }
            }

            // HANDLE MEDIA DATA
            /* @todo add to variation */
            /*
            if ($variationImages = $item->getData(VariationInterface::VARIATION_IMAGES)) {
                foreach ($variationImages as $variationImage) {
                    $variationImage[VariationInterface::ITEM_ID] = $itemId;
                    $variationImage[VariationInterface::VARIATION_ID] = $variationId;
                    $variationImage[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationImage[VariationInterface::SKU] = $sku;
                    $this->_variationMedia['add'] = $this->_prepareVariationMediaData($variationImage);
                }
            } */

            // HANDLE ATTRIBUTE VALUE DATA
            if ($variationAttributeValues = $item->getData(VariationInterface::VARIATION_ATTRIBUTE_VALUES)) {
                foreach ($variationAttributeValues as $variationAttributeValue) {
                    $variationAttributeValue[VariationInterface::ITEM_ID] = $itemId;
                    $variationAttributeValue[VariationInterface::VARIATION_ID] = $variationId;
                    $variationAttributeValue[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationAttributeValue[VariationInterface::SKU] = $sku;
                    $this->_variationAttributeValues[] = $this->_prepareVariationAttributeValueData($variationAttributeValue);
                }
            }

            // HANDLE TEXT DATA
            if ($variationTexts = $item->getData(VariationInterface::VARIATION_TEXTS)) {
                foreach ($variationTexts as $variationText) {
                    $variationText[VariationInterface::ITEM_ID] = $itemId;
                    $variationText[VariationInterface::VARIATION_ID] = $variationId;
                    $variationText[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationText[VariationInterface::SKU] = $sku;
                    $this->_variationTexts[] = $this->_prepareVariationTextData($variationText);
                }
            }

            // HANDLE STOCK DATA
            if ($variationStocks = $item->getData(VariationInterface::VARIATION_STOCK)) {
                foreach ($variationStocks as $variationStock) {
                    $variationStock[VariationInterface::ITEM_ID] = $itemId;
                    $variationStock[VariationInterface::VARIATION_ID] = $variationId;
                    $variationStock[VariationInterface::EXTERNAL_ID] = $externalId;
                    $variationStock[VariationInterface::SKU] = $sku;
                    $this->_variationStock[] = $this->_prepareVariationStockData($variationStock);
                }
            }
        }

        try {
            $this->_resource
                ->addMultiple($this->_variations);

            // Save properties
            if (!empty($this->_variationProperties)) {
                $this->_propertyResource->addMultiple($this->_variationProperties);
            }

            // Save barcodes
            if (!empty($this->_variationBarCodes)) {
                $this->_barCodeResource->addMultiple($this->_variationBarCodes);
            }

            // Save bundle components
            if (isset($this->_variationBundleComponents['add'])) {
                $this->_bundleResource->addMultiple(current($this->_variationBundleComponents));
            } else {
                $this->_bundleResource->removeEntry(current($this->_variationBundleComponents), VariationInterface::ITEM_ID);
            }

            // Save sales prices
            if (isset($this->_variationSalesPrices['add'])) {
                $this->_salesPriceResource->addMultiple(current($this->_variationSalesPrices));
            } else {
                $this->_salesPriceResource->removeEntry(current($this->_variationSalesPrices));
            }

            // Save market item numbers
            if (!empty($this->_marketItemNumbers)) {
                $this->_marketNumberResource->addMultiple($this->_marketItemNumbers);
            }

            // Save categories
            if (!empty($this->_variationCategories)) {
                $this->_categoryResource->addMultiple($this->_variationCategories);
            }

            // Save suppliers
            if (!empty($this->_variationSuppliers)) {
                $this->_supplierResource->addMultiple($this->_variationSuppliers);
            }

            // Save warehouses
            if (!empty($this->_variationWarehouses)) {
                $this->_warehouseResource->addMultiple($this->_variationWarehouses);
            }

            // Save images
            /*
            if (!empty($this->_variationMedia[])) {
                $this->_mediaResource->addMultiple($this->_variationMedia);
            } else {
                $this->_mediaResource->removeEntry($this->_variationMedia, VariationInterface::ITEM_ID);
            } */

            // Save attribute values
            if (!empty($this->_variationAttributeValues)) {
                $this->_attributeValueResource->addMultiple($this->_variationAttributeValues);
            }

            // Save texts
            if (!empty($this->_variationTexts)) {
                $this->_textsResource->addMultiple($this->_variationTexts);
            }

            // Save stock
            if (!empty($this->_variationStock)) {
                $this->_stockResource->addMultiple($this->_variationStock);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _resetDataContainers()
    {
        $this->_variations =
        $this->_properties =
        $this->_variationProperties =
        $this->_variationBarCodes =
        $this->_variationBundleComponents =
        $this->_variationSalesPrices =
        $this->_marketItemNumbers =
        $this->_variationCategories =
        $this->_variationSuppliers =
        $this->_variationWarehouses =
        $this->_variationMedia =
        $this->_variationAttributeValues =
        $this->_variationDescription =
        $this->_variationStock = [];
        return $this;
    }

    /**
     * @param VariationInterface $variation
     * @return array
     */
    private function _prepareVariationData($variation)
    {
        return [
            VariationInterface::ITEM_ID => $variation->getItemId(),
            VariationInterface::VARIATION_ID => $variation->getVariationId(),
            VariationInterface::MAIN_VARIATION_ID => $variation->getMainVariationId(),
            VariationInterface::EXTERNAL_ID => $variation->getExternalId(),
            VariationInterface::SKU => $variation->getSku(),
            VariationInterface::NAME => $variation->getName(),
            VariationInterface::STATUS => Status::PENDING,
            VariationInterface::IS_MAIN => $variation->getIsMain() ?? false,
            VariationInterface::IS_ACTIVE => $variation->getIsActive() ?? false,
            VariationInterface::CATEGORY_VARIATION_ID => $variation->getCategoryVariationId(),
            VariationInterface::MARKET_VARIATION_ID => $variation->getMarketVariationId(),
            VariationInterface::CLIENT_VARIATION_ID => $variation->getClientVariationId(),
            VariationInterface::SALES_PRICE_VARIATION_ID => $variation->getSalesPriceVariationId(),
            VariationInterface::SUPPLIER_VARIATION_ID => $variation->getSupplierVariationId(),
            VariationInterface::WAREHOUSE_VARIATION_ID => $variation->getWarehouseVariationId(),
            VariationInterface::PROPERTY_VARIATION_ID => $variation->getPropertyVariationId(),
            VariationInterface::POSITION => $variation->getPosition(),
            VariationInterface::MODEL => $variation->getModel(),
            VariationInterface::PARENT_VARIATION_ID => $variation->getParentVariationId(),
            VariationInterface::PARENT_VARIATION_QTY => $variation->getParentVariationQty(),
            VariationInterface::AVAILABILITY => $variation->getAvailability(),
            VariationInterface::FLAG_ONE => $variation->getFlagOne(),
            VariationInterface::FLAG_TWO => $variation->getFlagTwo(),
            VariationInterface::ESTIMATED_AVAILABLE_AT => $variation->getEstimatedAvailableAt(),
            VariationInterface::PURCHASE_PRICE => $variation->getPurchasePrice() ?? 0.0000,
            VariationInterface::RELATED_UPDATED_AT => $variation->getRelatedUpdatedAt(),
            VariationInterface::PRICE_CALCULATION_ID => $variation->getPriceCalculationId(),
            VariationInterface::PICKING => $variation->getPicking() ?? 0.0000,
            VariationInterface::STOCK_LIMITATION => $variation->getStockLimitation(),
            VariationInterface::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE => $variation->getIsVisibleIfNetStockIsPositive() ?? false,
            VariationInterface::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE => $variation->getIsInvisibleIfNetStockIsNotPositive() ?? false,
            VariationInterface::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE => $variation->getIsAvailableIfNetStockIsPositive() ?? false,
            VariationInterface::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE => $variation->getIsUnavailableIfNetStockIsNotPositive() ?? false,
            VariationInterface::MAIN_WAREHOUSE_ID => $variation->getMainWarehouseId(),
            VariationInterface::MAX_ORDER_QTY => $variation->getMaxOrderQty(),
            VariationInterface::MIN_ORDER_QTY => $variation->getMinOrderQty(),
            VariationInterface::INTERVAL_ORDER_QTY => $variation->getIntervalOrderQty(),
            VariationInterface::AVAILABLE_UNTIL => $variation->getAvailableUnit(),
            VariationInterface::RELEASED_AT => $variation->getReleasedAt(),
            VariationInterface::UNIT_COMBINATION_ID => $variation->getUnitCombinationId(),
            VariationInterface::WEIGHT_G => $variation->getWeightG(),
            VariationInterface::WEIGHT_NET_G => $variation->getWeightNetG(),
            VariationInterface::WIDTH_MM => $variation->getWidthMm(),
            VariationInterface::LENGTH_MM => $variation->getLengthMm(),
            VariationInterface::HEIGHT_MM => $variation->getHeightMm(),
            VariationInterface::EXTRA_SHIPPING_CHARGES1 => $variation->getExtraShippingCharge1() ?? 0.0000,
            VariationInterface::EXTRA_SHIPPING_CHARGES2 => $variation->getExtraShippingCharge2() ?? 0.0000,
            VariationInterface::UNITS_CONTAINED => $variation->getUnitsContained() ?? 1,
            VariationInterface::PALLET_TYPE_ID => $variation->getPalletTypeId(),
            VariationInterface::PACKING_UNITS => $variation->getPackingUnits(),
            VariationInterface::PACKING_UNITS_TYPE_ID => $variation->getPackingUnitTypeId(),
            VariationInterface::TRANSPORTATION_COSTS => $variation->getTransportationCosts() ?? 0.0000,
            VariationInterface::STORAGE_COSTS => $variation->getStorageCosts() ?? 0.0000,
            VariationInterface::CUSTOMS => $variation->getCustoms() ?? 0.0000,
            VariationInterface::OPERATION_COSTS => $variation->getOperationCosts() ?? 0.0000,
            VariationInterface::VAT_ID => $variation->getVatId(),
            VariationInterface::BUNDLE_TYPE => $variation->getBundleType(),
            VariationInterface::AUTO_CLIENT_VISIBILITY => $variation->getAutoClientVisibility() ?? 0,
            VariationInterface::IS_HIDDEN_IN_CATEGORY_LIST => $variation->getIsHiddenInCategoryList() ?? false,
            VariationInterface::DEFAULT_SHIPPING_COSTS => $variation->getDefaultShippingCosts() ?? 0.0000,
            VariationInterface::CAN_SHOW_UNIT_PRICE => $variation->getCanShowUnitPrice() ?? false,
            VariationInterface::MOVING_AVERAGE_PRICE => $variation->getMovingAveragePrice() ?? 0.0000,
            VariationInterface::AUTO_LIST_VISIBILITY => $variation->getAutoListVisibility(),
            VariationInterface::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE => $variation->getIsVisibleInListIfNetStockIsPositive() ?? false,
            VariationInterface::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE => $variation->getIsInvisibleInListIfNetStockIsNotPositive() ?? false,
            VariationInterface::SINGLE_ITEM_COUNT => $variation->getSingleItemCount(),
            VariationInterface::SALES_RANK => $variation->getSalesRank(),
            VariationInterface::VARIATION_CLIENTS => $this->_serializer->serialize($variation->getVariationClients()),
            VariationInterface::VARIATION_MARKETS => $this->_serializer->serialize($variation->getVariationMarkets()),
            VariationInterface::VARIATION_DEFAULT_CATEGORY => $this->_serializer->serialize($variation->getVariationDefaultCategory()),
            VariationInterface::VARIATION_SKUS => $this->_serializer->serialize($variation->getVariationSkus()),
            VariationInterface::VARIATION_ADDITIONAL_SKUS => $this->_serializer->serialize($variation->getVariationAdditionalSkus()),
            VariationInterface::VARIATION_UNIT => $this->_serializer->serialize($variation->getVariationUnit()),
            VariationInterface::CREATED_AT => $variation->getCreatedAt(),
            VariationInterface::UPDATED_AT => $variation->getUpdatedAt(),
            VariationInterface::COLLECTED_AT => $this->_dateTime->gmtDate(),
            VariationInterface::MESSAGE => __('Variation has been collected.')
        ];
    }

    /**
     * @param array $property
     * @return array
     */
    private function _prepareVariationPropertiesData(array $property)
    {
        return [
            PropertyInterface::ITEM_ID => $property[VariationInterface::ITEM_ID] ?? null,
            PropertyInterface::VARIATION_ID => $property[VariationInterface::VARIATION_ID] ?? null,
            PropertyInterface::EXTERNAL_ID => $property[VariationInterface::EXTERNAL_ID] ?? null,
            PropertyInterface::SKU => $property[VariationInterface::SKU] ?? null,
            PropertyInterface::PLENTY_ENTITY_ID => $property[PropertyDataInterface::ID] ?? null,
            PropertyInterface::PROPERTY_ID => $property[PropertyDataInterface::PROPERTY_ID] ?? null,
            PropertyInterface::PROPERTY_SELECTION_ID => $property[PropertyDataInterface::PROPERTY_SELECTION_ID] ?? null,
            PropertyInterface::NAMES => isset($property[PropertyDataInterface::NAMES])
                ? $this->_serializer->serialize($property[PropertyDataInterface::NAMES])
                : null,
            PropertyInterface::PROPERTY_SELECTION => isset($property[PropertyDataInterface::PROPERTY_SELECTION])
                ? $this->_serializer->serialize($property[PropertyDataInterface::PROPERTY_SELECTION])
                : null,
            PropertyInterface::PROPERTY => isset($property[PropertyDataInterface::PROPERTY])
                ? $this->_serializer->serialize($property[PropertyDataInterface::PROPERTY])
                : null,
            PropertyInterface::CREATED_AT => $property[PropertyDataInterface::CREATED_AT] ?? null,
            PropertyInterface::UPDATED_AT => $property[PropertyDataInterface::UPDATED_AT] ?? null,
            PropertyInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $barcode
     * @return array
     */
    private function _prepareVariationBarcodeData(array $barcode)
    {
        return [
            BarcodeInterface::ITEM_ID => $barcode[BarcodeInterface::ITEM_ID] ?? null,
            BarcodeInterface::VARIATION_ID => $barcode[BarcodeInterface::VARIATION_ID] ?? null,
            BarcodeInterface::EXTERNAL_ID => $barcode[BarcodeInterface::EXTERNAL_ID] ?? null,
            BarcodeInterface::SKU => $barcode[BarcodeInterface::SKU] ?? null,
            BarcodeInterface::BARCODE_ID => $barcode[BarcodeDataInterface::BARCODE_ID] ?? null,
            BarcodeInterface::CODE => $barcode[BarcodeDataInterface::CODE] ?? null,
            BarcodeInterface::CREATED_AT => $barcode[BarcodeDataInterface::CREATED_AT] ?? null,
            BarcodeInterface::UPDATED_AT => $barcode[BarcodeDataInterface::UPDATED_AT] ?? null,
            BarcodeInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $bundleComponent
     * @return array
     */
    private function _prepareVariationBundleComponentData(array $bundleComponent)
    {
        return [
            BundleInterface::ITEM_ID => $bundleComponent[BundleInterface::ITEM_ID] ?? null,
            BundleInterface::VARIATION_ID => $bundleComponent[BundleInterface::VARIATION_ID] ?? null,
            BundleInterface::EXTERNAL_ID => $bundleComponent[BundleInterface::EXTERNAL_ID] ?? null,
            BundleInterface::SKU => $bundleComponent[BundleInterface::SKU] ?? null,
            BundleInterface::VARIATION_BUNDLE_ID => $bundleComponent[BundleDataInterface::ID] ?? null,
            BundleInterface::COMPONENT_VARIATION_ID => $bundleComponent[BundleDataInterface::COMPONENT_VARIATION_ID] ?? null,
            BundleInterface::COMPONENT_QTY => $bundleComponent[BundleDataInterface::COMPONENT_QTY] ?? null,
            BundleInterface::CREATED_AT => $bundleComponent[BundleDataInterface::CREATED_AT] ?? null,
            BundleInterface::UPDATED_AT => $bundleComponent[BundleDataInterface::UPDATED_AT] ?? null,
            BundleInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $salesPrice
     * @return array
     */
    private function _prepareVariationSalesPriceData(array $salesPrice)
    {
        return [
            SalesPriceInterface::ITEM_ID => $salesPrice[SalesPriceInterface::ITEM_ID] ?? null,
            SalesPriceInterface::VARIATION_ID => $salesPrice[SalesPriceInterface::VARIATION_ID] ?? null,
            SalesPriceInterface::EXTERNAL_ID => $salesPrice[SalesPriceInterface::EXTERNAL_ID] ?? null,
            SalesPriceInterface::SKU => $salesPrice[SalesPriceInterface::SKU] ?? null,
            SalesPriceInterface::SALES_PRICE_ID => $salesPrice[SalesPriceDataInterface::SALES_PRICE_ID] ?? null,
            SalesPriceInterface::PRICE => $salesPrice[SalesPriceDataInterface::PRICE] ?? null,
            SalesPriceInterface::CREATED_AT =>  $salesPrice[SalesPriceDataInterface::CREATED_AT] ?? null,
            SalesPriceInterface::UPDATED_AT => $salesPrice[SalesPriceDataInterface::UPDATED_AT] ?? null,
            SalesPriceInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $marketItemNumber
     * @return array
     */
    private function _prepareMarketItemNumberData(array $marketItemNumber)
    {
        return [
            MarketNumberInterface::ITEM_ID => $marketItemNumber[MarketNumberInterface::ITEM_ID] ?? null,
            MarketNumberInterface::VARIATION_ID => $marketItemNumber[MarketNumberInterface::VARIATION_ID] ?? null,
            MarketNumberInterface::EXTERNAL_ID => $marketItemNumber[MarketNumberInterface::EXTERNAL_ID] ?? null,
            MarketNumberInterface::SKU => $marketItemNumber[MarketNumberInterface::SKU] ?? null,
            MarketNumberInterface::PLENTY_ENTITY_ID => $marketItemNumber[MarketNumberDataInterface::ID] ?? null,
            MarketNumberInterface::COUNTRY_ID => $marketItemNumber[MarketNumberDataInterface::COUNTRY_ID] ?? null,
            MarketNumberInterface::TYPE => $marketItemNumber[MarketNumberDataInterface::TYPE] ?? null,
            MarketNumberInterface::POSITION => $marketItemNumber[MarketNumberDataInterface::POSITION] ?? null,
            MarketNumberInterface::VALUE => $marketItemNumber[MarketNumberDataInterface::VALUE] ?? null,
            MarketNumberInterface::CREATED_AT => $marketItemNumber[MarketNumberDataInterface::CREATED_AT] ?? null,
            MarketNumberInterface::UPDATED_AT => $marketItemNumber[MarketNumberDataInterface::UPDATED_AT] ??null,
            MarketNumberInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $category
     * @return array
     */
    private function _prepareVariationCategoryData(array $category)
    {
        return [
            CategoryInterface::ITEM_ID => $category[CategoryInterface::ITEM_ID] ?? null,
            CategoryInterface::VARIATION_ID => $category[CategoryInterface::VARIATION_ID] ?? null,
            CategoryInterface::EXTERNAL_ID => isset($category[CategoryInterface::EXTERNAL_ID])
                ? $category[CategoryInterface::EXTERNAL_ID]
                : null,
            CategoryInterface::SKU => isset($category[CategoryInterface::SKU])
                ? $category[CategoryInterface::SKU]
                : null,
            CategoryInterface::CATEGORY_ID => isset($category[CategoryDataInterface::CATEGORY_ID])
                ? $category[CategoryDataInterface::CATEGORY_ID]
                : null,
            CategoryInterface::POSITION => isset($category[CategoryDataInterface::POSITION])
                ? $category[CategoryDataInterface::POSITION]
                : null,
            CategoryInterface::IS_NECKERMANN_PRIMARY => isset($category[CategoryDataInterface::IS_NECKERMANN_PRIMARY])
                ? $category[CategoryDataInterface::IS_NECKERMANN_PRIMARY]
                : null,
            CategoryInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $supplier
     * @return array
     */
    private function _prepareVariationSupplierData(array $supplier)
    {
        return [
            SupplierInterface::ITEM_ID => isset($supplier[SupplierInterface::ITEM_ID])
                ? $supplier[SupplierInterface::ITEM_ID]
                : null,
            SupplierInterface::VARIATION_ID => isset($supplier[SupplierInterface::VARIATION_ID])
                ? $supplier[SupplierInterface::VARIATION_ID]
                : null,
            SupplierInterface::EXTERNAL_ID => isset($supplier[SupplierInterface::EXTERNAL_ID])
                ? $supplier[SupplierInterface::EXTERNAL_ID]
                : null,
            SupplierInterface::SKU => isset($supplier[SupplierInterface::SKU])
                ? $supplier[SupplierInterface::SKU]
                : null,
            SupplierInterface::PLENTY_ENTITY_ID => isset($supplier[SupplierDataInterface::ID])
                ? $supplier[SupplierDataInterface::ID]
                : null,
            SupplierInterface::SUPPLIER_ID => isset($supplier[SupplierDataInterface::SUPPLIER_ID])
                ? $supplier[SupplierDataInterface::SUPPLIER_ID]
                : null,
            SupplierInterface::PURCHASE_PRICE => isset($supplier[SupplierDataInterface::PURCHASE_PRICE])
                ? $supplier[SupplierDataInterface::PURCHASE_PRICE]
                : null,
            SupplierInterface::MINIMUM_PURCHASE => isset($supplier[SupplierDataInterface::MINIMUM_PURCHASE])
                ? $supplier[SupplierDataInterface::MINIMUM_PURCHASE]
                : null,
            SupplierInterface::ITEM_NUMBER => isset($supplier[SupplierDataInterface::ITEM_NUMBER])
                ? $supplier[SupplierDataInterface::ITEM_NUMBER]
                : null,
            SupplierInterface::LAST_PRICE_QUERY => isset($supplier[SupplierDataInterface::LAST_PRICE_QUERY])
                ? $supplier[SupplierDataInterface::LAST_PRICE_QUERY]
                : null,
            SupplierInterface::DELIVERY_TIME_IN_DAYS => isset($supplier[SupplierDataInterface::DELIVERY_TIME_IN_DAYS])
                ? $supplier[SupplierDataInterface::DELIVERY_TIME_IN_DAYS]
                : null,
            SupplierInterface::DISCOUNT => isset($supplier[SupplierDataInterface::DISCOUNT])
                ? $supplier[SupplierDataInterface::DISCOUNT]
                : null,
            SupplierInterface::IS_DISCOUNTABLE => isset($supplier[SupplierDataInterface::IS_DISCOUNTABLE])
                ? $supplier[SupplierDataInterface::IS_DISCOUNTABLE]
                : null,
            SupplierInterface::PACKAGING_UNIT => isset($supplier[SupplierDataInterface::PACKAGING_UNIT])
                ? $supplier[SupplierDataInterface::PACKAGING_UNIT]
                : null,
            SupplierInterface::CREATED_AT => isset($supplier[SupplierDataInterface::CREATED_AT])
                ? $supplier[SupplierDataInterface::CREATED_AT]
                : null,
            SupplierInterface::UPDATED_AT => isset($supplier[SupplierDataInterface::UPDATED_AT])
                ? $supplier[SupplierDataInterface::UPDATED_AT]
                : null,
            SupplierInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $warehouse
     * @return array
     */
    private function _prepareVariationWarehouseData(array $warehouse)
    {
        return [
            WarehouseInterface::ITEM_ID => isset($warehouse[WarehouseInterface::ITEM_ID])
                ? $warehouse[WarehouseInterface::ITEM_ID]
                : null,
            WarehouseInterface::VARIATION_ID => isset($warehouse[WarehouseInterface::VARIATION_ID])
                ? $warehouse[WarehouseInterface::VARIATION_ID]
                : null,
            WarehouseInterface::EXTERNAL_ID => isset($warehouse[WarehouseInterface::EXTERNAL_ID])
                ? $warehouse[WarehouseInterface::EXTERNAL_ID]
                : null,
            WarehouseInterface::SKU => isset($warehouse[WarehouseInterface::SKU])
                ? $warehouse[WarehouseInterface::SKU]
                : null,
            WarehouseInterface::WAREHOUSE_ID => isset($warehouse[WarehouseDataInterface::WAREHOUSE_ID])
                ? $warehouse[WarehouseDataInterface::WAREHOUSE_ID]
                : null,
            WarehouseInterface::WAREHOUSE_ZONE_ID => isset($warehouse[WarehouseDataInterface::WAREHOUSE_ZONE_ID])
                ? $warehouse[WarehouseDataInterface::WAREHOUSE_ZONE_ID]
                : null,
            WarehouseInterface::STORAGE_LOCATION_TYPE => isset($warehouse[WarehouseDataInterface::STORAGE_LOCATION_TYPE])
                ? $warehouse[WarehouseDataInterface::STORAGE_LOCATION_TYPE]
                : null,
            WarehouseInterface::REORDER_LEVEL => isset($warehouse[WarehouseDataInterface::REORDER_LEVEL])
                ? $warehouse[WarehouseDataInterface::REORDER_LEVEL]
                : null,
            WarehouseInterface::MAX_STOCK => isset($warehouse[WarehouseDataInterface::MAX_STOCK])
                ? $warehouse[WarehouseDataInterface::MAX_STOCK]
                : null,
            WarehouseInterface::STOCK_TURNOVER_IN_DAYS => isset($warehouse[WarehouseDataInterface::STOCK_TURNOVER_IN_DAYS])
                ? $warehouse[WarehouseDataInterface::STOCK_TURNOVER_IN_DAYS]
                : null,
            WarehouseInterface::STORAGE_LOCATION => isset($warehouse[WarehouseDataInterface::STORAGE_LOCATION])
                ? $warehouse[WarehouseDataInterface::STORAGE_LOCATION]
                : null,
            WarehouseInterface::STOCK_BUFFER => isset($warehouse[WarehouseDataInterface::STOCK_BUFFER])
                ? $warehouse[WarehouseDataInterface::STOCK_BUFFER]
                : null,
            WarehouseInterface::IS_BATCH => isset($warehouse[WarehouseDataInterface::IS_BATCH])
                ? $warehouse[WarehouseDataInterface::IS_BATCH]
                : null,
            WarehouseInterface::IS_BEST_BEFORE_DATE => isset($warehouse[WarehouseDataInterface::IS_BEST_BEFORE_DATE])
                ? $warehouse[WarehouseDataInterface::IS_BEST_BEFORE_DATE]
                : null,
            WarehouseInterface::CREATED_AT => isset($warehouse[WarehouseDataInterface::CREATED_AT])
                ? $warehouse[WarehouseDataInterface::CREATED_AT]
                : null,
            WarehouseInterface::UPDATED_AT => isset($warehouse[WarehouseDataInterface::UPDATED_AT])
                ? $warehouse[WarehouseDataInterface::UPDATED_AT]
                : null,
            WarehouseInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $media
     * @return array
     */
    private function _prepareVariationMediaData(array $media)
    {
        return [
            WarehouseInterface::ITEM_ID => isset($media[WarehouseInterface::ITEM_ID])
                ? $media[WarehouseInterface::ITEM_ID]
                : null,
            WarehouseInterface::VARIATION_ID => isset($media[WarehouseInterface::VARIATION_ID])
                ? $media[WarehouseInterface::VARIATION_ID]
                : null,
            WarehouseInterface::EXTERNAL_ID => isset($media[WarehouseInterface::EXTERNAL_ID])
                ? $media[WarehouseInterface::EXTERNAL_ID]
                : null,
            WarehouseInterface::SKU => isset($media[WarehouseInterface::SKU])
                ? $media[WarehouseInterface::SKU]
                : null,
            MediaInterface::MEDIA_ID => isset($media[MediaDataInterface::ID])
                ? $media[MediaDataInterface::ID]
                : null,
            MediaInterface::MEDIA_TYPE => MediaInterface::MEDIA_TYPE_VARIATION,
            MediaInterface::TYPE => isset($media[MediaDataInterface::TYPE])
                ? $media[MediaDataInterface::TYPE]
                : null,
            MediaInterface::FILE_TYPE => isset($media[MediaDataInterface::FILE_TYPE])
                ? $media[MediaDataInterface::FILE_TYPE]
                : null,
            MediaInterface::PATH => isset($media[MediaDataInterface::PATH])
                ? $media[MediaDataInterface::PATH]
                : null,
            MediaInterface::POSITION => isset($media[MediaDataInterface::POSITION])
                ? $media[MediaDataInterface::POSITION]
                : null,
            MediaInterface::MD5_CHECKSUM => isset($media[MediaDataInterface::MD5_CHECKSUM])
                ? $media[MediaDataInterface::MD5_CHECKSUM]
                : null,
            MediaInterface::MD5_CHECKSUM_ORIGINAL => isset($media[MediaDataInterface::MD5_CHECKSUM_ORIGINAL])
                ? $media[MediaDataInterface::MD5_CHECKSUM_ORIGINAL]
                : null,
            MediaInterface::WIDTH => isset($media[MediaDataInterface::WIDTH])
                ? $media[MediaDataInterface::WIDTH]
                : null,
            MediaInterface::HEIGHT => isset($media[MediaDataInterface::HEIGHT])
                ? $media[MediaDataInterface::HEIGHT]
                : null,
            MediaInterface::SIZE => isset($media[MediaDataInterface::SIZE])
                ? $media[MediaDataInterface::SIZE]
                : null,
            MediaInterface::STORAGE_PROVIDER_ID => isset($media[MediaDataInterface::STORAGE_PROVIDER_ID])
                ? $media[MediaDataInterface::STORAGE_PROVIDER_ID]
                : null,
            MediaInterface::CLEAN_IMAGE_NAME => isset($media[MediaDataInterface::CLEAN_IMAGE_NAME])
                ? $media[MediaDataInterface::CLEAN_IMAGE_NAME]
                : null,
            MediaInterface::URL => isset($media[MediaDataInterface::URL])
                ? $media[MediaDataInterface::URL]
                : null,
            MediaInterface::URL_MIDDLE => isset($media[MediaDataInterface::URL_MIDDLE])
                ? $media[MediaDataInterface::URL_MIDDLE]
                : null,
            MediaInterface::URL_PREVIEW => isset($media[MediaDataInterface::URL_PREVIEW])
                ? $media[MediaDataInterface::URL_PREVIEW]
                : null,
            MediaInterface::URL_SECONDARY_PREVIEW => isset($media[MediaDataInterface::URL_SECONDARY_PREVIEW])
                ? $media[MediaDataInterface::URL_SECONDARY_PREVIEW]
                : null,
            MediaInterface::DOCUMENT_UPLOAD_PATH => isset($media[MediaDataInterface::DOCUMENT_UPLOAD_PATH])
                ? $media[MediaDataInterface::DOCUMENT_UPLOAD_PATH]
                : null,
            MediaInterface::DOCUMENT_UPLOAD_PATH_PREVIEW => isset($media[MediaDataInterface::DOCUMENT_UPLOAD_PATH_PREVIEW])
                ? $media[MediaDataInterface::DOCUMENT_UPLOAD_PATH_PREVIEW]
                : null,
            MediaInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH => isset($media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH])
                ? $media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH]
                : null,
            MediaInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT => isset($media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT])
                ? $media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT]
                : null,
            MediaInterface::AVAILABILITIES => isset($media[MediaDataInterface::AVAILABILITIES])
                ? $this->_serializer->serialize($media[MediaDataInterface::AVAILABILITIES])
                : null,
            MediaInterface::NAMES => isset($media[MediaDataInterface::NAMES])
                ? $this->_serializer->serialize($media[MediaDataInterface::NAMES])
                : null,
            MediaInterface::CREATED_AT => isset($media[MediaDataInterface::CREATED_AT])
                ? $media[MediaDataInterface::CREATED_AT]
                : null,
            MediaInterface::UPDATED_AT => isset($media[MediaDataInterface::UPDATED_AT])
                ? $media[MediaDataInterface::UPDATED_AT]
                : null,
            MediaInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $attributeValue
     * @return array
     */
    private function _prepareVariationAttributeValueData(array $attributeValue)
    {
        return [
            AttributeValueInterface::ITEM_ID => isset($attributeValue[AttributeValueInterface::ITEM_ID])
                ? $attributeValue[AttributeValueInterface::ITEM_ID]
                : null,
            AttributeValueInterface::VARIATION_ID => isset($attributeValue[AttributeValueInterface::VARIATION_ID])
                ? $attributeValue[AttributeValueInterface::VARIATION_ID]
                : null,
            AttributeValueInterface::EXTERNAL_ID => isset($attributeValue[AttributeValueInterface::EXTERNAL_ID])
                ? $attributeValue[AttributeValueInterface::EXTERNAL_ID]
                : null,
            AttributeValueInterface::SKU => isset($attributeValue[AttributeValueInterface::SKU])
                ? $attributeValue[AttributeValueInterface::SKU]
                : null,
            AttributeValueInterface::ATTRIBUTE_VALUE_SET_ID => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE_SET_ID])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE_SET_ID]
                : null,
            AttributeValueInterface::ATTRIBUTE_ID => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_ID])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_ID]
                : null,
            AttributeValueInterface::VALUE_ID => isset($attributeValue[AttributeValueDataInterface::VALUE_ID])
                ? $attributeValue[AttributeValueDataInterface::VALUE_ID]
                : null,
            AttributeValueInterface::IS_LINKED_TO_IMAGE => isset($attributeValue[AttributeValueDataInterface::IS_LINKED_TO_IMAGE])
                ? $attributeValue[AttributeValueDataInterface::IS_LINKED_TO_IMAGE]
                : null,
            AttributeValueInterface::ATTRIBUTE_BACKEND_NAME => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::BACKEND_NAME])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::BACKEND_NAME]
                : null,
            AttributeValueInterface::ATTRIBUTE_POSITION => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::POSITION])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::POSITION]
                : null,
            AttributeValueInterface::VALUE_BACKEND_NAME => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::BACKEND_NAME])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::BACKEND_NAME]
                : null,
            AttributeValueInterface::VALUE_POSITION => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::POSITION])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::POSITION]
                : null,
            AttributeValueInterface::IS_SURCHARGE_PERCENTAGE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::IS_SURCHARGE_PERCENTAGE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::IS_SURCHARGE_PERCENTAGE]
                : null,
            AttributeValueInterface::AMAZON_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::AMAZON_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::AMAZON_ATTRIBUTE]
                : null,
            AttributeValueInterface::FRUUGO_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::FRUUGO_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::FRUUGO_ATTRIBUTE]
                : null,
            AttributeValueInterface::PIXMANIA_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::PIXMANIA_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::PIXMANIA_ATTRIBUTE]
                : null,
            AttributeValueInterface::OTTO_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::OTTO_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::OTTO_ATTRIBUTE]
                : null,
            AttributeValueInterface::GOOGLE_SHOPPING_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::GOOGLE_SHOPPING_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::GOOGLE_SHOPPING_ATTRIBUTE]
                : null,
            AttributeValueInterface::NECKERMANN_AT_EP_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::NECKERMANN_AT_EP_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::NECKERMANN_AT_EP_ATTRIBUTE]
                : null,
            AttributeValueInterface::TYPE_OF_SELECTION_IN_ONLINE_STORE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::TYPE_OF_SELECTION_IN_ONLINE_STORE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::TYPE_OF_SELECTION_IN_ONLINE_STORE]
                : null,
            AttributeValueInterface::LA_REDOUTE_ATTRIBUTE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::LA_REDOUTE_ATTRIBUTE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::LA_REDOUTE_ATTRIBUTE]
                : null,
            AttributeValueInterface::IS_GROUPABLE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::IS_GROUPABLE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::IS_GROUPABLE]
                : null,
            AttributeValueInterface::VALUE_IMAGE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::IMAGE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::IMAGE]
                : null,
            AttributeValueInterface::VALUE_COMMENT => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::COMMENT])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::COMMENT]
                : null,
            AttributeValueInterface::AMAZON_VALUE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::AMAZON_VALUE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::AMAZON_VALUE]
                : null,
            AttributeValueInterface::OTTO_VALUE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::OTTO_VALUE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::OTTO_VALUE]
                : null,
            AttributeValueInterface::NECKERMANN_AT_EP_VALUE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::NECKERMANN_AT_EP_VALUE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::NECKERMANN_AT_EP_VALUE]
                : null,
            AttributeValueInterface::LA_REDOUTE_VALUE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::LA_REDOUTE_VALUE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::LA_REDOUTE_VALUE]
                : null,
            AttributeValueInterface::TRACDELIGHT_VALUE => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::TRACDELIGHT_VALUE])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::TRACDELIGHT_VALUE]
                : null,
            AttributeValueInterface::PERCENTAGE_DISTRIBUTION => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::PERCENTAGE_DISTRIBUTION])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::PERCENTAGE_DISTRIBUTION]
                : null,
            AttributeValueInterface::ATTRIBUTE_UPDATED_AT => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::UPDATED_AT])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE][AttributeValueDataInterface::UPDATED_AT]
                : null,
            AttributeValueInterface::VALUE_UPDATED_AT => isset($attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::UPDATED_AT])
                ? $attributeValue[AttributeValueDataInterface::ATTRIBUTE_VALUE][AttributeValueDataInterface::UPDATED_AT]
                : null,
            AttributeValueInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $item
     * @return array
     */
    private function _prepareVariationTextData(array $item)
    {
        return [
            TextsInterface::ITEM_ID => isset($item[TextsInterface::ITEM_ID])
                ? $item[TextsInterface::ITEM_ID]
                : null,
            TextsInterface::VARIATION_ID => isset($item[TextsInterface::VARIATION_ID])
                ? $item[TextsInterface::VARIATION_ID]
                : null,
            TextsInterface::EXTERNAL_ID => isset($item[TextsInterface::EXTERNAL_ID])
                ? $item[TextsInterface::EXTERNAL_ID]
                : null,
            TextsInterface::SKU => isset($item[TextsInterface::SKU])
                ? $item[TextsInterface::SKU]
                : null,
            TextsInterface::LANG => isset($item[TextDataInterface::LANG])
                ? $item[TextDataInterface::LANG]
                : null,
            TextsInterface::NAME => isset($item[TextDataInterface::NAME])
                ? $item[TextDataInterface::NAME]
                : null,
            TextsInterface::NAME2 => isset($item[TextDataInterface::NAME2])
                ? $item[TextDataInterface::NAME2]
                : null,
            TextsInterface::NAME3 => isset($item[TextDataInterface::NAME3])
                ? $item[TextDataInterface::NAME3]
                : null,
            TextsInterface::SHORT_DESCRIPTION => isset($item[TextDataInterface::SHORT_DESCRIPTION])
                ? $item[TextDataInterface::SHORT_DESCRIPTION]
                : null,
            TextsInterface::DESCRIPTION => isset($item[TextDataInterface::DESCRIPTION])
                ? $item[TextDataInterface::DESCRIPTION]
                : null,
            TextsInterface::TECHNICAL_DATA => isset($item[TextDataInterface::TECHNICAL_DATA])
                ? $item[TextDataInterface::TECHNICAL_DATA]
                : null,
            TextsInterface::URL_PATH => isset($item[TextDataInterface::URL_PATH])
                ? $item[TextDataInterface::URL_PATH]
                : null,
            TextsInterface::META_DESCRIPTION => isset($item[TextDataInterface::META_DESCRIPTION])
                ? $item[TextDataInterface::META_DESCRIPTION]
                : null,
            TextsInterface::META_KEYWORDS => isset($item[TextDataInterface::META_KEYWORDS])
                ? $item[TextDataInterface::META_KEYWORDS]
                : null,
            TextsInterface::CREATED_AT => isset($item[TextDataInterface::CREATED_AT])
                ? $item[TextDataInterface::CREATED_AT]
                : null,
            TextsInterface::UPDATED_AT => isset($item[TextDataInterface::UPDATED_AT])
                ? $item[TextDataInterface::UPDATED_AT]
                : null,
            TextsInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $stock
     * @return array
     */
    private function _prepareVariationStockData(array $stock)
    {
        return [
            StockInterface::ITEM_ID => isset($stock[StockInterface::ITEM_ID])
                ? $stock[StockInterface::ITEM_ID]
                : null,
            StockInterface::VARIATION_ID => isset($stock[StockInterface::VARIATION_ID])
                ? $stock[StockInterface::VARIATION_ID]
                : null,
            StockInterface::EXTERNAL_ID => isset($stock[StockInterface::EXTERNAL_ID])
                ? $stock[StockInterface::EXTERNAL_ID]
                : null,
            StockInterface::SKU => isset($stock[StockInterface::SKU])
                ? $stock[StockInterface::SKU]
                : null,
            StockInterface::WAREHOUSE_ID => isset($stock[StockDataInterface::WAREHOUSE_ID])
                ? $stock[StockDataInterface::WAREHOUSE_ID]
                : null,
            StockInterface::PURCHASE_PRICE => isset($stock[StockDataInterface::PURCHASE_PRICE])
                ? $stock[StockDataInterface::PURCHASE_PRICE]
                : null,
            StockInterface::RESERVED_LISTING => isset($stock[StockDataInterface::RESERVED_LISTING])
                ? $stock[StockDataInterface::RESERVED_LISTING]
                : null,
            StockInterface::RESERVED_BUNDLES => isset($stock[StockDataInterface::RESERVED_BUNDLES])
                ? $stock[StockDataInterface::RESERVED_BUNDLES]
                : null,
            StockInterface::PHYSICAL_STOCK => isset($stock[StockDataInterface::PHYSICAL_STOCK])
                ? $stock[StockDataInterface::PHYSICAL_STOCK]
                : null,
            StockInterface::RESERVED_STOCK => isset($stock[StockDataInterface::RESERVED_STOCK])
                ? $stock[StockDataInterface::RESERVED_STOCK]
                : null,
            StockInterface::NET_STOCK => isset($stock[StockDataInterface::NET_STOCK])
                ? $stock[StockDataInterface::NET_STOCK]
                : null,
            StockInterface::REORDER_LEVEL => isset($stock[StockDataInterface::REORDER_LEVEL])
                ? $stock[StockDataInterface::REORDER_LEVEL]
                : null,
            StockInterface::DELTA_REORDER_LEVEL => isset($stock[StockDataInterface::DELTA_REORDER_LEVEL])
                ? $stock[StockDataInterface::DELTA_REORDER_LEVEL]
                : null,
            StockInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }
}
