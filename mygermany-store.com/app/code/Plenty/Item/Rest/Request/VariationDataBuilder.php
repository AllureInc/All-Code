<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Profile\Export\Entity\Product as ProfileProductEntity;
use Plenty\Item\Profile\Config\Source\Product\Attribute\Weight;

/**
 * Class VariationDataBuilder
 * @package Plenty\Item\Rest\Request
 */
class VariationDataBuilder implements VariationDataInterface
{
    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var ProfileProductEntity|null
     */
    private $_profileEntity;

    /**
     * @var StockRegistryInterface
     */
    private $_stockRegistry;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * VariationDataBuilder constructor.
     * @param Helper $helper
     * @param StockRegistryInterface $stockRegistry
     * @param DateTime $dateTime
     */
    public function __construct(
        Helper $helper,
        StockRegistryInterface $stockRegistry,
        DateTime $dateTime
    ) {
        $this->_helper = $helper;
        $this->_stockRegistry = $stockRegistry;
        $this->_dateTime = $dateTime;
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
     * @param CatalogProduct $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function buildRequest(CatalogProduct $product)
    {
        $this->_request = [];

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Product item id is not set. [SKU: %1]', $product->getSku()));
        }

        $isMain = $product->getData(ProductInterface::IS_MAIN_PRODUCT);
        $variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID);
        $mainVariationId = $product->getData(ProductInterface::MAIN_VARIATION_ID);

        $this->_buildMainAttributeData($product);

        // model number
        $modelNumberMapping = $this->getProfileEntity()->getModelNumber();
        if ($modelNumberMapping &&
            $modelNumber = $product->getData($modelNumberMapping)
        ) {
            $this->_request['model'] = $modelNumber;
        }

        // add categories
        if ($isMain && $categories = $product->getData(ProductExportManagementInterface::REQUEST_CATEGORY)) {
            $this->_request['variationCategories'] = $categories;
        }

        // assign bundle type
        if ($product->getTypeId() == CatalogProduct\Type::TYPE_BUNDLE) {
            $this->_request['bundleType'] = CatalogProduct\Type::TYPE_BUNDLE;
        }

        // add weight
        if ($product->getWeight()
            && $product->getWeight() != 0
            && $weightUnit = $this->getProfileEntity()->getDefaultWeightUnit()
        ) {
            if (Weight\Unit\Plenty::WEIGHT_G === $weightUnit) {
                $weightUnit = 'weightG';
            } else {
                $weightUnit = 'weightGNet';
            }
            $this->_request[$weightUnit] = (float) $product->getWeight();
        }

        // tax
        if ($taxMapping = $this->getProfileEntity()->getTaxMapping()) {
            if (isset($taxMapping[$product->getData('tax_class_id')])) {
                $this->_request['vatId'] = (int) $taxMapping[$product->getData('tax_class_id')];
            }
        }

        // Purchase price
        if ($purchasePrice = $this->_getPurchasePrice($product)) {
            $this->_request['purchasePrice'] = $purchasePrice;
        }

        // Warehouse Id
        if ($mainWarehouseId = $this->getProfileEntity()->getMainWarehouseId()) {
            $this->_request['mainWarehouseId'] = $mainWarehouseId;
        }

        // Order picking
        if ($orderPicking = $this->getProfileEntity()->getShippingOrderPicking()) {
            $this->_request['picking'] = $orderPicking;
        }

        // Dimensions width mm
        $itemWidthMapping = $this->getProfileEntity()->getItemWidthMapping();
        if ($itemWidthMapping
            && $width = $product->getData($itemWidthMapping)
        ) {
            $adjustment = $this->getProfileEntity()->getDimensionsAdjustments()
                ? $this->getProfileEntity()->getDimensionsAdjustments()
                : 1;
            $this->_request['widthMM'] = $width * $adjustment;
        }

        // Dimensions length mm
        $itemLengthMapping = $this->getProfileEntity()->getItemLengthMapping();
        if ($itemLengthMapping
            && $length = $product->getData($itemLengthMapping)
        ) {
            $adjustment = $this->getProfileEntity()->getDimensionsAdjustments()
                ? $this->getProfileEntity()->getDimensionsAdjustments()
                : 1;
            $this->_request['lengthMM'] = $length * $adjustment;
        }

        // Dimensions height mm
        $itemHeightMapping = $this->getProfileEntity()->getItemHeightMapping();
        if ($itemHeightMapping
            && $height = $product->getData($itemHeightMapping)
        ) {
            $adjustment = $this->getProfileEntity()->getDimensionsAdjustments()
                ? $this->getProfileEntity()->getDimensionsAdjustments()
                : 1;
            $this->_request['lengthMM'] = $height * $adjustment;
        }

        if ($mainVariationId) {
            $this->_request['categoryVariationId'] = $mainVariationId;
            $this->_request['clientVariationId'] = $mainVariationId;
            // $this->_request['salesPriceVariationId'] = $mainVariationId;
            $this->_request['supplierVariationId'] = $mainVariationId;
            $this->_request['warehouseVariationId'] = $mainVariationId;
        }

        if (false === $isMain && $variationId) {
            $this->_request['marketVariationId'] = $variationId;
        }

        $this->_buildConfigAttributeData($product);

        return $this->_request;
    }

    /**
     * @param CatalogProduct $product
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _buildMainAttributeData(CatalogProduct $product)
    {
        $isMain = $product->getData(ProductInterface::IS_MAIN_PRODUCT);
        $mainVariationId = $product->getData(ProductInterface::MAIN_VARIATION_ID);

        $stockItem = $this->_stockRegistry->getStockItem($product->getId());
        $product->setData(ProductExportManagementInterface::STOCK_ITEM_OBJ, $stockItem);

        $this->_request = [
            'isMain' => $isMain,
            'mainVariationId' => $mainVariationId,
            'itemId' => $product->getData(ProductInterface::PLENTY_ITEM_ID),
            // 'position' => 0,
            'isActive' => $product->getStatus() == 1,
            'number' => $product->getSku(),
            // 'model' => '',
            'externalId' => $product->getId(),
            'availability' => 1, // Possible values: 1 to 10.
            // 'estimatedAvailableAt' => '',
            // 'purchasePrice'                         => $this->_getPurchasePrice($product),
            // 'priceCalculationId' => 0,
            // 'picking'                               => $this->getProfileEntity()->getOrderPicking(),
            // 'stockLimitation'                       => 0,
            'isVisibleIfNetStockIsPositive' => true,
            'isInvisibleIfNetStockIsNotPositive' => false,
            'isAvailableIfNetStockIsPositive' => true,
            'isUnavailableIfNetStockIsNotPositive' => true,
            // 'mainWarehouseId' => $this->getProfileEntity()->getWarehouseId(),
            /*
            'maximumOrderQuantity' => $stock && $stock->getMaxSaleQty()
                ? $stock->getMaxSaleQty()
                : 0, */
            'minimumOrderQuantity' => $stockItem && $stockItem->getMinSaleQty()
                ? $stockItem->getMinSaleQty()
                : 1,
            'intervalOrderQuantity' => $stockItem && $stockItem->getQtyIncrements()
                ? $stockItem->getQtyIncrements()
                : 0,
            // 'availableUntil' => '',
            'releasedAt' => $product->getCreatedAt()
                ? $product->getCreatedAt()
                : $this->_dateTime->gmtDate(),
            'name' => $product->getName(),
            // 'extraShippingCharge1' => 0,
            // 'extraShippingCharge2' => 0,
            // 'unitsContained' => 1,
            // 'palletTypeId' => 0,
            // 'packingUnits' => 0,
            // 'packingUnitTypeId' => 0,
            // 'transportationCosts' => 0,
            // 'storageCosts' => 0,
            // 'customs' => 0,
            // 'operatingCosts' => 0,
            // 'automaticClientVisibility' => '',
            'isHiddenInCategoryList' => false,
            // 'mayShowUnitPrice' => '',
            // 'variationBarcodes' => [],
            // 'variationSalesPrices' => [],
            'variationClients' => [['plentyId' => $this->_helper->getPlentyId()]],
            // 'variationMarkets'                      => [],
            // 'variationDefaultCategory'              => [],
            // 'variationSuppliers'                    => [],
            // 'variationWarehouses'                   => [],
            // 'variationAttributeValues' => $variationAttributeValues,
            'unit' => ['unitId' => 1, 'content' => 1]
        ];

        return $this;
    }

    /**
     * @param CatalogProduct $product
     * @return $this
     */
    protected function _buildConfigAttributeData(CatalogProduct $product)
    {
        if (false === $product->getData(ProductInterface::IS_MAIN_PRODUCT)
            && $product->getData(ProductInterface::PLENTY_ITEM_ID)
            && $configAttributes = $product->getData(ProductExportManagementInterface::CONFIG_ATTRIBUTES)
        ) {
            foreach ($configAttributes as $configAttribute) {
                if (!isset($configAttribute['id'])
                    || !isset($configAttribute['backendName'])
                    || !isset($configAttribute['values'])
                ) {
                    continue;
                }

                if (!$attributeValue = $product->getAttributeText($configAttribute['backendName'])) {
                    continue;
                }

                $valueId = $this->getSearchArrayMatch($attributeValue, $configAttribute['values'], 'backendName', 'id');
                if (false === $valueId) {
                    continue;
                }

                $this->_request['variationAttributeValues'][] = [
                    'attributeId' => $configAttribute['id'],
                    'valueId' => $valueId
                ];
            }
        }

        return $this;
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $columnName
     * @param null $columnId
     * @return false|int|string
     */
    private function getSearchArrayMatch(
        $needle, array $haystack, $columnName, $columnId = null
    ) {
        if ($columnId) {
            return array_search($needle, array_column($haystack, $columnName, $columnId));
        }
        return array_search($needle, array_column($haystack, $columnName));
    }

    /**
     * @param CatalogProduct $product
     * @return float|mixed
     * @throws \Exception
     */
    private function _getPurchasePrice(CatalogProduct $product)
    {
        if (!$purchasePriceMapper = $this->getProfileEntity()->getPurchasePriceMapping()) {
            return 0.0000;
        }

        return $product->getData($purchasePriceMapper);
    }
}