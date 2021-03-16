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
use Plenty\Item\Profile\Export\Entity\Product as ProfileProductEntity;
use Plenty\Item\Model\ResourceModel\Import\Item\Texts\Collection;
use Plenty\Item\Model\ResourceModel\Import\Item\Texts\CollectionFactory;
use Plenty\Item\Helper;

/**
 * Class TextDataBuilder
 * @package Plenty\Item\Rest\Request\Item
 */
class TextDataBuilder implements TextDataInterface
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
     * @var ProfileProductEntity
     */
    private $_profileEntity;

    /**
     * @var CollectionFactory
     */
    private $_textCollectionFactory;

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
     * TextDataBuilder constructor.
     * @param Helper\Data $itemHelper
     * @param CollectionFactory $textCollectionFactory
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Helper\Data $itemHelper,
        CollectionFactory $textCollectionFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->_helper = $itemHelper;
        $this->_textCollectionFactory = $textCollectionFactory;
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
     */
    public function buildBatchRequest(CatalogProduct $product)
    {
        $this->_request = [];

        if (false === $product->getData(ProductInterface::IS_MAIN_PRODUCT)) {
            return $this->_request;
        }

        if (!$itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID)) {
            throw new \Exception(__('Item is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$variationId = $product->getData(ProductInterface::PLENTY_VARIATION_ID)) {
            throw new \Exception(__('Variation is not set. [SKU: %1]', $product->getSku()));
        }

        if (!$mappedStores = $this->getProfileEntity()->getStoreMapping()) {
            throw new \Exception(__('Stores are not mapped. [Trace: %1]', __METHOD__));
        }

        /** @var Collection $itemTexts */
        $itemTexts = $this->_textCollectionFactory->create();
        $itemTexts->addFieldToFilter('variation_id', $variationId)
            ->load();

        $productResource = $product->getResource();
        foreach ($mappedStores as $mappedStore) {
            $isNew = true;
            $mageStore = $mappedStore->getData(ProductExportInterface::MAGE_STORE_CODE);
            $plentyStore = $mappedStore->getData(ProductExportInterface::PLENTY_STORE);
            $store = $this->_storeManager->getStore($mageStore);

            if (!in_array($store->getId(), $product->getStoreIds())) {
                continue;
            }

            if (in_array($plentyStore, $itemTexts->getColumnValues('lang'))) {
                $isNew = false;
            }

            $requestBody = [
                'itemId' => $itemId,
                'lang' => $plentyStore
            ];

            // add name
            $nameMapping = $this->getProfileEntity()->getNameMapping();
            if (!empty($nameMapping)) {
                foreach ($nameMapping as $mageName => $plentyName) {
                    if (!$name = $productResource->getAttributeRawValue($product->getId(), $mageName, $store)) {
                        continue;
                    }
                    $requestBody[$plentyName] = $name;
                }
            } else {
                $requestBody['name'] = $productResource->getAttributeRawValue($product->getId(), 'name', $store);
            }

            // add short description
            if ($shortDescriptionMapping = $this->getProfileEntity()->getShortDescriptionMapping()) {
                if ($shortDescription = $productResource->getAttributeRawValue($product->getId(), $shortDescriptionMapping, $store)) {
                    $requestBody['previewDescription'] = $shortDescription;
                }
            }

            // add description
            if ($descriptionMapping = $this->getProfileEntity()->getDescriptionMapping()) {
                if ($description = $productResource->getAttributeRawValue($product->getId(), $descriptionMapping, $store)) {
                    $requestBody['description'] = $description;
                }
            }

            // add technical data
            if ($technicalDataMapping = $this->getProfileEntity()->getTechnicalDataMapping()) {
                if ($technicalData = $productResource->getAttributeRawValue($product->getId(), $technicalDataMapping, $store)) {
                    $requestBody['technicalData'] = $technicalData;
                }
            }

            // add meta description
            if ($metaDescription = $productResource->getAttributeRawValue($product->getId(), 'meta_description', $store)) {
                $requestBody['metaDescription'] = $metaDescription;
            }

            // add meta keywords
            if ($keywords = $productResource->getAttributeRawValue($product->getId(), 'meta_keyword', $store)) {
                $requestBody['metaKeywords'] = $keywords;
            }

            $this->_request['payloads'][] = [
                'resource' => $isNew
                    ? $this->_helper->getVariationDescriptionUrl($itemId, $variationId)
                    : $this->_helper->getVariationDescriptionUrl($itemId, $variationId, $plentyStore),
                'method' => $isNew
                    ? 'POST'
                    : 'PUT',
                'body' => $requestBody
            ];
        }

        return $this->_request;
    }
}