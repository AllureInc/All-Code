<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request\Item;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\Collection;
use Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\CollectionFactory;
use Plenty\Item\Helper;

/**
 * Class SalesPriceDataBuilder
 * @package Plenty\Item\Rest\Request\Item
 */
class SalesPriceDataBuilder implements SalesPriceDataInterface
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
    private $_salesPriceCollectionFactory;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $_productAttributeRepository;

    /**
     * @var array
     */
    private $_errors;

    /**
     * SalesPriceDataBuilder constructor.
     * @param Helper\Data $helper
     * @param CollectionFactory $salesPriceCollectionFactory
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Helper\Data $helper,
        CollectionFactory $salesPriceCollectionFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->_helper = $helper;
        $this->_salesPriceCollectionFactory = $salesPriceCollectionFactory;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_productAttributeRepository = $productAttributeRepository;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function buildBatchRequest(CatalogProduct $product)
    {
        $this->_request = [];

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Variation is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$mappedStores = $this->getProfileEntity()->getStoreMapping()) {
            throw new \Exception(__('Stores are not mapped. [Trace: %1]', __METHOD__));
        }

        /** @var Collection $collection */
        $collection = $this->_salesPriceCollectionFactory->create();
        $collection->addFieldToFilter('variation_id', $variationId)
            ->load();
        $itemSalesPrices = $collection->getData();

        $salesPriceId = null;
        $productResource = $product->getResource();
        foreach ($mappedStores as $mappedStore) {
            $mageStore = $mappedStore->getData(ProductExportInterface::MAGE_STORE_CODE);
            $store = $this->_storeManager->getStore($mageStore);

            if (!in_array($store->getId(), $product->getStoreIds())) {
                continue;
            }

            if (!$priceMapping = $this->getProfileEntity()->getPriceMapping($mageStore)) {
                $this->_errors[] = __('Product sales prices are not mapped. [Store: %s]', $mageStore);
                continue;
            }

            foreach ($priceMapping as $magePriceCode => $plentyPriceId) {
                if (isset($this->_request[$plentyPriceId])
                    || (!$priceValue = (float) $productResource->getAttributeRawValue($product->getId(), $magePriceCode, $store))
                ) {
                    continue;
                }

                // Update existing record
                $record = $this->_helper->getSearchArrayMatch($plentyPriceId, $itemSalesPrices, 'sales_price_id');
                if (false !== $record
                    && isset($itemSalesPrices[$record])
                    && isset($itemSalesPrices[$record]['price'])
                    && isset($itemSalesPrices[$record]['sales_price_id'])
                    && ($itemSalesPrices[$record]['sales_price_id'] == $plentyPriceId)
                ) {
                    $recordPriceValue = (float) $itemSalesPrices[$record]['price'];
                    if ($priceValue != $recordPriceValue) {
                        $this->_request[$plentyPriceId] = [
                            'resource' => $this->_helper->getVariationSalesPricesUrl($itemId, $variationId, $plentyPriceId),
                            'method' => 'PUT',
                            'body' => [
                                'variationId' => $variationId,
                                'salesPriceId' => $plentyPriceId,
                                'price' => $priceValue
                            ]
                        ];
                    } else {
                        $this->_request[$plentyPriceId] = [];
                    }

                    unset($itemSalesPrices[$record]);
                    $itemSalesPrices = array_values($itemSalesPrices);
                    continue;
                }

                // Creating new record
                $this->_request[$plentyPriceId] = [
                    'resource' => $this->_helper->getVariationSalesPricesUrl($itemId, $variationId),
                    'method' => 'POST',
                    'body' => [
                        'variationId' => $variationId,
                        'salesPriceId' => $plentyPriceId,
                        'price' => $priceValue
                    ]
                ];
            }
        }

        // Remove unlinked entries
        if ($this->getProfileEntity()->getIsActiveSalesPriceDelete()) {
            foreach ($itemSalesPrices as $salesRecord) {
                if (!isset($salesRecord['sales_price_id'])) {
                    continue;
                }
                $this->_request[$salesRecord['sales_price_id']] = [
                    'resource' => $this->_helper->getVariationSalesPricesUrl($itemId, $variationId, $salesRecord['sales_price_id']),
                    'method' => 'DELETE',
                    'body' => []
                ];
            }
        }

        // Filter out empty request
        $this->_request = array_filter($this->_request, function($value) {return !empty($value);});

        return empty($this->_request)
            ? $this->_request
            : ['payloads' => $this->_request];
    }
}