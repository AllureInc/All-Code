<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request\Item;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Helper\Data as ItemHelper;
use Plenty\Item\Profile\Export\Entity\Product as ProfileProductEntity;
use Plenty\Item\Model\Import\Item\MarketNumber;
use Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\Collection;
use Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\CollectionFactory;
use Plenty\Core\Model\Profile\Config\Source\Countries;

/**
 * Class MarketNumberDataBuilder
 * @package Plenty\Item\Rest\Request\Item
 */
class MarketNumberDataBuilder implements MarketNumberDataInterface
{
    /**
     * @var ItemHelper
     */
    private $_helper;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var ProfileProductEntity
     */
    private $_profileEntity;

    /**
     * @var CollectionFactory
     */
    private $_marketNumberCollectionFactory;

    /**
     * @var Countries
     */
    private $_countrySource;

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
     * MarketNumberDataBuilder constructor.
     * @param ItemHelper $itemHelper
     * @param CollectionFactory $marketNumberCollectionFactory
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param Countries $countries
     */
    public function __construct(
        ItemHelper $itemHelper,
        CollectionFactory $marketNumberCollectionFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        Countries $countries
    ) {
        $this->_helper = $itemHelper;
        $this->_marketNumberCollectionFactory = $marketNumberCollectionFactory;
        $this->_countrySource = $countries;
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

        if (!$marketNumberMapping = $this->getProfileEntity()->getMarketNumberMapping()) {
            return $this->_request;
        }

        $productResource = $product->getResource();
        foreach ($mappedStores as $mappedStore) {
            $mageStore = $mappedStore->getData(ProductExportInterface::MAGE_STORE_CODE);
            $store = $this->_storeManager->getStore($mageStore);
            $countryId = $this->_countrySource->getCountryIdByStoreCode($mageStore);

            if (!in_array($store->getId(), $product->getStoreIds())) {
                continue;
            }

            foreach ($marketNumberMapping as $mageCode => $plentyCode) {
                $marketNumberValue = $productResource->getAttributeRawValue($product->getId(), $mageCode, $store);

                /** @var Collection $collection */
                $collection = $this->_marketNumberCollectionFactory->create();
                $collection->addFieldToFilter('variation_id', $variationId)
                    ->addFieldToFilter('type', $plentyCode)
                    ->addFieldToFilter('country_id', $countryId);
                /** @var MarketNumber $itemMarketNumber */
                $itemMarketNumber = $collection->getFirstItem();

                // Create new record
                if (!$collection->getSize() && $marketNumberValue) {
                    $this->_request["{$plentyCode}_{$countryId}"] = [
                        'resource' => $this->_helper->getVariationMarketNumbersUrl($itemId, $variationId),
                        'method' => 'POST',
                        'body' => [
                            'variationId' => $variationId,
                            'countryId' => $countryId,
                            'type' => $plentyCode,
                            'value' => $marketNumberValue
                        ]
                    ];
                    continue;
                }

                // Update existing record
                if ($marketNumberValue && ($marketNumberValue !== $itemMarketNumber->getValue())) {
                    $this->_request["{$plentyCode}_{$countryId}"] = [
                        'resource' => $this->_helper->getVariationMarketNumbersUrl($itemId, $variationId, $itemMarketNumber->getPlentyEntityId()),
                        'method' => 'PUT',
                        'body' => [
                            'variationId' => $variationId,
                            'countryId' => $countryId,
                            'type' => $plentyCode,
                            'value' => $marketNumberValue
                        ]
                    ];
                    continue;
                }

                // Delete record
                if (!$marketNumberValue && $itemMarketNumber->getValue()) {
                    $this->_request["{$plentyCode}_{$countryId}"] = [
                        'resource' => $this->_helper->getVariationMarketNumbersUrl($itemId, $variationId, $itemMarketNumber->getPlentyEntityId()),
                        'method' => 'DELETE',
                        'body' => []
                    ];
                }
            }
        }

        return empty($this->_request)
            ? $this->_request
            : ['payloads' => $this->_request];
    }
}