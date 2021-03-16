<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

use Plenty\Item\Api\Data\Import\Item\BundleInterface;
use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductVariationExportManagementInterface;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Profile\Config\Source\Product\Attribute\Weight;
use Plenty\Item\Rest\Variation as VariationClient;
use Plenty\Item\Rest\Request\Item\MarketNumberDataBuilder;
use Plenty\Item\Rest\Request\Item\SalesPriceDataBuilder;
use Plenty\Item\Rest\Request\Item\StockDataBuilder;
use Plenty\Item\Rest\Request\Item\TextDataBuilder;
use Plenty\Item\Rest\Request\VariationDataBuilder;
use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;

/**
 * Class ProductVariationExportManagement
 * @package Plenty\Item\Profile
 */
class ProductVariationExportManagement extends AbstractManagement
    implements ProductVariationExportManagementInterface
{
    /**
     * @var Product
     */
    private $_product;

    /**
     * @var Product\ActionFactory
     */
    private $_productActionFactory;

    /**
     * @var ConfigurableProduct
     */
    private $_configurableProduct;

    /**
     * @var MarketNumberDataBuilder
     */
    private $_marketNumberDataBuilder;

    /**
     * @var SalesPriceDataBuilder
     */
    private $_salesPriceDataBuilder;

    /**
     * @var StockDataBuilder
     */
    private $_stockDataBuilder;

    /**
     * @var TextDataBuilder
     */
    private $_textDataBuilder;

    /**
     * @var VariationDataBuilder
     */
    private $_variationDataBuilder;

    /**
     * @var ResourceModel\Import\Item\Bundle\CollectionFactory
     */
    private $_bundleCollectionFactory;

    /**
     * ProductVariationExportManagement constructor.
     * @param VariationClient $variationClient
     * @param MarketNumberDataBuilder $marketNumberDataBuilder
     * @param SalesPriceDataBuilder $salesPriceDataBuilder
     * @param StockDataBuilder $stockDataBuilder
     * @param TextDataBuilder $textDataBuilder
     * @param VariationDataBuilder $variationDataBuilder
     * @param Product\ActionFactory $productActionFactory
     * @param ResourceModel\Import\Item\Bundle\CollectionFactory $collectionFactory
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        VariationClient $variationClient,
        MarketNumberDataBuilder $marketNumberDataBuilder,
        SalesPriceDataBuilder $salesPriceDataBuilder,
        StockDataBuilder $stockDataBuilder,
        TextDataBuilder $textDataBuilder,
        VariationDataBuilder $variationDataBuilder,
        Product\ActionFactory $productActionFactory,
        ResourceModel\Import\Item\Bundle\CollectionFactory $collectionFactory,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_client = $variationClient;
        $this->_marketNumberDataBuilder = $marketNumberDataBuilder;
        $this->_salesPriceDataBuilder = $salesPriceDataBuilder;
        $this->_stockDataBuilder = $stockDataBuilder;
        $this->_textDataBuilder = $textDataBuilder;
        $this->_variationDataBuilder = $variationDataBuilder;
        $this->_productActionFactory = $productActionFactory;
        $this->_bundleCollectionFactory = $collectionFactory;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return ProductExportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): ProductExportInterface
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
     * @param CatalogProduct $product
     * @return $this|ProductVariationExportManagementInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute(Product $product)
    {
        $this->_initialize($product);

        // Creat main variation
        $this->_createVariation($product);

        // Handle product type: configurable
        $this->_processTypeConfigurable($product);

        // Handle product type: bundle
        $this->_processTypeBundle($product);

        return $this;
    }

    /**
     * @param CatalogProduct $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createVariation(Product $product)
    {
        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID);

        $this->_variationDataBuilder->setProfileEntity($this->getProfileEntity());
        $request = $this->_variationDataBuilder->buildRequest($product);
        $response = $this->_client->createVariation($request, $itemId, $variationId);

        if ($this->_client->getErrors()) {
            $this->_response[$resId]['error'][] = __('Could not export variation. [SKU: %1]',
                $product->getSku());
            return $this;
        }

        if (isset($response['id'])) {
            $product->setData(ProductInterface::PLENTY_VARIATION_ID, (int) $response['id']);
            $this->_response[$resId]['item_variation']['success'][] = __('Variation has been %1. [SKU: %2].',
                $variationId
                    ? 'updated'
                    : 'created',
                $product->getSku()
            );
        }

        if (!$variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Plenty variation ID is not set. [SKU: %1]', $product->getSku()));
        }

        $this->_createTexts($product)
            ->_createSalesPrices($product)
            ->_createStock($product)
            ->_createBarCodes($product)
            ->_createMarketNumbers($product)
            ->createCharacteristics($product)
            ->createProperties($product)
            ->_updateProductPlentyData($product);

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createTexts(Product $product)
    {
        if (false === $product->getData(ProductInterface::IS_MAIN_PRODUCT)) {
            return $this;
        }

        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $this->_textDataBuilder->setProfileEntity($this->getProfileEntity());
        $request = $this->_textDataBuilder->buildBatchRequest($product);
        $response = $this->_client->getBatchProcess($request);
        foreach ($response as $item) {
            if (!isset($item['method']) || !isset($item['content'])) {
                continue;
            }

            $action = 'deleted';
            if ($item['method'] === 'POST') {
                $action = 'created';
            } elseif ($item['method'] === 'PUT') {
                $action = 'updated';
            }

            $content = $this->_serializer->unserialize($item['content']);
            if (isset($content->error)) {
                $this->_response[$resId]['texts']['error'][]
                    = __('Variation texts could not be %1. [SKU: %2, Reason: %3]',
                    $action, $product->getSku(), isset($content->error->message) ? $content->error->message : '');
            } else {
                $this->_response[$resId]['texts']['success'][]
                    = __('Variation texts have been %1. [SKU: %2, Response: %3]',
                    $action, $product->getSku(), $item['content']);
            }
        }

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createSalesPrices(Product $product)
    {
        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        if (!$this->getProfileEntity()->getIsActiveSalesPriceExport()) {
            $this->_response[$resId]['sales_price']['notice'][] = __('Sale price export is disabled. [SKU: %1]',
                $product->getSku());
            return $this;
        }

        $this->_salesPriceDataBuilder->setProfileEntity($this->getProfileEntity());
        $request = $this->_salesPriceDataBuilder->buildBatchRequest($product);

        if (empty($request) && empty($this->_salesPriceDataBuilder->getErrors())) {
            $this->_response[$resId]['sales_price']['success'][] = __('Sales prices are up to date. [SKU: %1]',
                $product->getSku());
            return $this;
        } elseif (empty($request) && $errors = $this->_salesPriceDataBuilder->getErrors()) {
            $this->_response[$resId]['sales_price']['error'][] = __('Failed creating sales price(s). [SKU: %1, Reason: %2]',
                $product->getSku(),
                implode(', ', $errors)
            );
            return $this;
        }

        $response = $this->_client->getBatchProcess($request);
        foreach ($response as $item) {
            if (!isset($item['method']) || !isset($item['content'])) {
                continue;
            }

            $action = 'deleted';
            if ($item['method'] === 'POST') {
                $action = 'created';
            } elseif ($item['method'] === 'PUT') {
                $action = 'updated';
            }

            $content = $this->_serializer->unserialize($item['content']);

            if (isset($content->error)) {
                $this->_response[$resId]['sales_price']['error'][] = __('Sales price could not be %1. [SKU: %2, Reason: %3]',
                    $action,
                    $product->getSku(),
                    isset($content->error->message)
                        ? $content->error->message
                        : ''
                );
            } else {
                $this->_response[$resId]['sales_price']['success'][] = __('Sales price has been %1. [SKU: %2, Response: %3]',
                    $action,
                    $product->getSku(),
                    $item['content']
                );
            }
        }

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createStock(Product $product)
    {
        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        if (!$this->getProfileEntity()->getIsActiveExportStock()) {
            $this->_response[$resId]['stock']['notice'][] = __('Stock export is inactive. [SKU: %1]',
                $product->getSku()
            );
            return $this;
        }

        if ($product->getTypeId() == ConfigurableProduct::TYPE_CODE
            || $product->getTypeId() == Product\Type::TYPE_BUNDLE
        ) {
            return $this;
        }

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Variation is not set. [SKU: %1]', $product->getSku()));
        }


        $isNew = $product->getData(ProductExportManagement::IS_NEW_EXPORT);
        $this->_stockDataBuilder->setProfileEntity($this->getProfileEntity());
        $request = $this->_stockDataBuilder->buildRequest($product);

        if (empty($request) && empty($this->_stockDataBuilder->getErrors())) {
            $this->_response[$resId]['stock']['success'][] = __('Stock is up to date. [SKU: %1]',
                $product->getSku()
            );
            return $this;
        } elseif (empty($request) && $errors = $this->_stockDataBuilder->getErrors()) {
            $this->_response[$resId]['stock']['error'][] = __('Failed to %1 stock. [SKU: %2, Reason: %3]',
                $isNew ? 'create' : 'update',
                $product->getSku(),
                implode(', ', $errors)
            );
            return $this;
        }

        $response = $this->_client->createVariationStock($request,  $itemId, $variationId);

        if ($this->_client->getErrors()) {
            $this->_response[$resId]['stock']['error'][] = __('Failed to %1 stock. [SKU: %2, Reason: %3]',
                $isNew ? 'create' : 'update',
                $product->getSku(),
                $this->_client->getErrors()
            );
            return $this;
        }

        foreach ($response as $item) {
            if (!isset($item['variationId'])
                || !isset($item['warehouseId'])
                || !isset($item['netStock'])
            ) {
                continue;
            }
            $this->_response[$resId]['stock']['success'][] = __('Stock has been %1. [SKU: %2, Variation %3, Warehouse: %4, Qty: %5]',
                $isNew ? 'created' : 'updated',
                $product->getSku(),
                $item['variationId'],
                $item['warehouseId'],
                $item['netStock']
            );
        }

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    private function _createBarCodes(Product $product)
    {
        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $this->_response[$resId]['barcodes']['notice'][] = __('Barcodes not implemented yet');

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createMarketNumbers(Product $product)
    {
        if ($product->getTypeId() != Product\Type::TYPE_SIMPLE) {
            return $this;
        }

        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $this->_marketNumberDataBuilder->setProfileEntity($this->getProfileEntity());
        $request = $this->_marketNumberDataBuilder->buildBatchRequest($product);

        if (empty($request) && empty($this->_marketNumberDataBuilder->getErrors())) {
            $this->_response[$product->getId()]['market_numbers']['success'][]
                = __('Market item numbers are up to date. [SKU: %1]', $product->getSku());
            return $this;
        }

        $response = $this->_client->getBatchProcess($request);

        foreach ($response as $item) {
            if (!isset($item['method']) || !isset($item['content'])) {
                continue;
            }

            $action = 'deleted';
            if ($item['method'] === 'POST') {
                $action = 'created';
            } elseif ($item['method'] === 'PUT') {
                $action = 'updated';
            }

            $content = $this->_serializer->unserialize($item['content']);
            if (isset($content->error)) {
                $this->_response[$resId]['market_numbers']['error'][] = __('Market item number could not be %1. [SKU: %2, Reason: %3]',
                    $action,
                    $product->getSku(),
                    isset($content->error->message)
                        ? $content->error->message
                        : ''
                );
            } else {
                $this->_response[$resId]['market_numbers']['success'][] = __('Item market number has been %1. [SKU: %2, Response: %3]',
                    $action,
                    $product->getSku(),
                    $item['content']
                );
            }
        }

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    protected function createCharacteristics(Product $product)
    {
        if (false === $product->getData(ProductInterface::IS_MAIN_PRODUCT)) {
            return $this;
        }

        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $this->_response[$resId]['barcodes']['notice'][] = __('Characteristics not implemented yet.');

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    protected function createProperties(Product $product)
    {
        if (false === $product->getData(ProductInterface::IS_MAIN_PRODUCT)) {
            return $this;
        }

        $resId = "{$product->getId()}_sku_{$product->getSku()}";
        $this->_response[$resId]['barcodes']['notice'][] = __('Properties not implemented yet.');
        return $this;
    }

    /**
     * @param CatalogProduct $product
     * @return $this|bool
     * @throws \Exception
     */
    protected function _processTypeConfigurable(CatalogProduct $product)
    {
        if ($product->getTypeId() != ConfigurableProduct::TYPE_CODE) {
            return false;
        }

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$mainVariationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Variation is not set. [SKU: %1]', $product->getSku()));
        }

        $itemResponse = $product->getData(ItemInterface::ITEM_RESPONSE);
        $itemVariations = isset($itemResponse['item_variations'])
            ? $itemResponse['item_variations']
            : [];

        /** @var ConfigurableProduct $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $children = $typeInstance->getUsedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();

        /** @var Product $child */
        foreach ($children as $child) {
            $child->setData(ProductInterface::IS_MAIN_PRODUCT, false);
            $child->setData(ProductInterface::MAIN_VARIATION_ID, $mainVariationId);
            $child->setData(ProductInterface::PLENTY_ITEM_ID, $itemId);

            $childVariationId = $this->getSearchArrayMatch($child->getSku(), $itemVariations, 'number', 'id');

            if ($childVariationId
                && (!$child->getData(ProductInterface::PLENTY_VARIATION_ID)
                    || $child->getData(ProductInterface::PLENTY_VARIATION_ID) != $childVariationId)
            ) {
                $child->setData(ProductInterface::PLENTY_VARIATION_ID, $childVariationId);
            } elseif (!$childVariationId && $child->getData(ProductInterface::PLENTY_VARIATION_ID)) {
                $child->setData(ProductInterface::PLENTY_VARIATION_ID, null);
            }

            $child->setData(ProductExportManagement::CONFIG_ATTRIBUTES, $product->getData(ProductExportManagement::CONFIG_ATTRIBUTES));

            $this->_createVariation($child);
        }

        return $this;
    }

    /**
     * @param CatalogProduct $product
     * @return $this|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _processTypeBundle(Product $product)
    {
        if ($product->getTypeId() != BundleProductType::TYPE_CODE) {
            return $this;
        }

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Variation is not set. [SKU: %1]', $product->getSku()));
        }

        /** @var BundleProductType $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $optionIds = $typeInstance->getOptionsIds($product);
        $bundleComponents = $typeInstance->getSelectionsCollection($optionIds, $product);
        $bundleComponentRecord = $this->_bundleCollectionFactory->create()
            ->addItemFilter($itemId)
            ->joinVariationEntries()
            ->getData();

        $request =
        $requestResponse =
        $bundleComponentFlag = [];
        $resId = "{$product->getId()}_sku_{$product->getSku()}";

        /** @var Product $bundleComponent */
        foreach ($bundleComponents as $bundleComponent) {
            $componentQty = (int) $bundleComponent->getSelectionQty();
            if (!$componentVariationId = $bundleComponent->getData(ProductInterface::PLENTY_VARIATION_ID)) {
                $this->_response[$resId]['bundle_component']['error'][] = __('Variation is not set. [SKU: %1, SKU: %2, Trace: %3]',
                    $product->getSku(),
                    $bundleComponent->getSku(),
                    __METHOD__
                );
                continue;
            }

            if (isset($request[$componentVariationId])) {
                continue;
            }

            // Update existing record
            $record = $this->getSearchArrayMatch(
                $componentVariationId,
                $bundleComponentRecord,
                BundleInterface::COMPONENT_VARIATION_ID
            );

            if (false !== $record
                && isset($bundleComponentRecord[$record][BundleInterface::SKU])
                && isset($bundleComponentRecord[$record][BundleInterface::COMPONENT_QTY])
                && isset($bundleComponentRecord[$record][BundleInterface::VARIATION_BUNDLE_ID])
                && isset($bundleComponentRecord[$record][BundleInterface::COMPONENT_VARIATION_ID])
                && ($bundleComponentRecord[$record][BundleInterface::COMPONENT_VARIATION_ID] == $componentVariationId)
            ) {
                if ($componentQty != $bundleComponentRecord[$record][BundleInterface::COMPONENT_QTY]) {
                    $request[$componentVariationId] = [
                        'resource' => $this->_helper->getVariationBundleUrl(
                            $itemId, $variationId, (int) $bundleComponentRecord[$record][BundleInterface::VARIATION_BUNDLE_ID]),
                        'method' => 'PUT',
                        'body' => [
                            'variationId' => $variationId,
                            'componentVariationId' => $componentVariationId,
                            'componentQuantity' => $componentQty
                        ]
                    ];
                    $requestResponse[] = __('Bundle component has been updated. [SKU: %1, Component SKU: %2, Component Qty: %3]',
                        $product->getSku(),
                        $bundleComponent->getSku(),
                        $componentQty
                    );
                } else {
                    $request[$componentVariationId] = [];
                    $requestResponse[] = __('Bundle component exists. [SKU: %1, Component SKU: %2, Component Qty: %3]',
                        $product->getSku(),
                        $bundleComponent->getSku(),
                        $componentQty
                    );
                }

                unset($bundleComponentRecord[$record]);
                $bundleComponentRecord = array_values($bundleComponentRecord);
                continue;
            }

            // Create new record
            $request[$componentVariationId] = [
                'resource' => $this->_helper->getVariationBundleUrl($itemId, $variationId),
                'method' => 'POST',
                'body' => [
                    'variationId' => $variationId,
                    'componentVariationId' => $componentVariationId,
                    'componentQuantity' => $componentQty
                ]
            ];
            $requestResponse[] = __('Bundle component has been created. [SKU: %1, Component SKU: %2, Component Qty: %3]',
                $product->getSku(),
                $bundleComponent->getSku(),
                $componentQty
            );
        }

        // Remove unlinked entries
        foreach ($bundleComponentRecord as $record) {
            if (!isset($record['variation_bundle_id'])
                && !isset($record['component_variation_id'])
                && !isset($record['sku'])
            ) {
                continue;
            }
            $request[$record[BundleInterface::COMPONENT_VARIATION_ID]] = [
                'resource' => $this->_helper->getVariationBundleUrl(
                    $itemId, $variationId, (int) $record['variation_bundle_id']),
                'method' => 'DELETE',
                'body' => []
            ];
            $requestResponse[] = __('Bundle component has been removed. [SKU: %1, Component SKU: %2]',
                $product->getSku(), $record['sku']);
        }

        // Filter out empty request
        $request = array_filter($request, function($value) {return !empty($value);});
        if (empty($request)) {
            $this->_response[$resId]['bundle']['success'] = $requestResponse;
            return $this;
        }

        $response = $this->_client->getBatchProcess(['payloads' => $request]);
        if (empty($response)) {
            $this->_response[$resId]['bundle']['error'][] = __(
                'Could not export bundle components. Referer to log for details. [SKU: %1, Reason: %2]',
                $product->getSku(),
                $this->_client->getErrors()
            );
            return $this;
        }

        if ($this->_client->getErrors()) {
            $this->_response[$resId]['bundle']['notice'][] = __(
                'Bundle components have been exported with some errors. Referer to log for details. [SKU: %1, Reason: %2]',
                $product->getSku(),
                $this->_client->getErrors()
            );
            return $this;
        }

        $this->_response[$resId]['bundle']['success'] = $requestResponse;

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    private function _initialize(Product $product)
    {
        $this->_response = null;
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Product
     * @throws \Exception
     */
    private function _getProduct()
    {
        if (!$this->_product) {
            throw new \Exception(__('Product is not set.'));
        }
        return $this->_product;
    }

    /**
     * @param CatalogProduct $product
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     */
    private function _initChildrenProducts(Product $product)
    {
        return $this->_configurableProduct->getUsedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();
    }

    /**
     * @param Product $product
     * @return $this
     */
    private function _updateProductPlentyData(Product $product)
    {
        /** @var Product\Action $productAction */
        $productAction = $this->_productActionFactory->create();

        $productAction->updateAttributes([$product->getId()], [
            ProductInterface::PLENTY_ITEM_ID => $product->getData(ProductInterface::PLENTY_ITEM_ID),
            ProductInterface::PLENTY_VARIATION_ID => $product->getData(ProductInterface::PLENTY_VARIATION_ID),
        ], 0);

        return $this;
    }
}