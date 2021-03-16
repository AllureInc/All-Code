<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Export;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Model\Logger;
use Plenty\Item\Helper\Data as Helper;

/**
 * Class Product
 * @package Plenty\Item\Model\Export
 *
 * @method ResourceModel\Export\Product getResource()
 * @method ResourceModel\Export\Product\Collection getCollection()
 */
class Product extends ImportExportAbstract
    implements ProductInterface, IdentityInterface
{
    const CACHE_TAG = 'plenty_item_product';

    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var array
     */
    protected $_error;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Export\Product::class);
    }

    /**
     * Product constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param ProductCollectionFactory $productCollectionFactory
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
        ProductCollectionFactory $productCollectionFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->setData(self::PROFILE_ID, $profileId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->setData(self::ITEM_ID, $itemId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    /**
     * @param $variationId
     * @return $this
     */
    public function setVariationId($variationId)
    {
        $this->setData(self::VARIATION_ID, $variationId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMainVariationId()
    {
        return $this->getData(self::MAIN_VARIATION_ID);
    }

    /**
     * @param int $mainVariationId
     * @return $this
     */
    public function setMainVariationId($mainVariationId)
    {
        $this->setData(self::MAIN_VARIATION_ID, $mainVariationId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    /**
     * @param $productType
     * @return $this
     */
    public function setProductType($productType)
    {
        $this->setData(self::PRODUCT_TYPE, $productType);
        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeSet()
    {
        return $this->getData(self::ATTRIBUTE_SET);
    }

    /**
     * @param $attributeSet
     * @return $this
     */
    public function setAttributeSet($attributeSet)
    {
        $this->setData(self::ATTRIBUTE_SET, $attributeSet);
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->getData(self::VISIBILITY);
    }

    /**
     * @param $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->setData(self::VISIBILITY, $visibility);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        if (!$entries = $this->getData(self::ENTRIES)) {
            return [];
        }
        return $this->_serializer->unserialize($entries);
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function setEntries(array $entries)
    {
        $this->setData(self::ENTRIES, $this->_serializer->serialize($entries));
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCreatedAt($dateTime)
    {
        $this->setData(self::CREATED_AT, $dateTime);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setUpdatedAt($dateTime)
    {
        $this->setData(self::UPDATED_AT, $dateTime);
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setProcessedAt($dateTime)
    {
        $this->setData(self::PROCESSED_AT, $dateTime);
        return $this;
    }

    /**
     * @param int $profileId
     * @param array $productIds
     * @return $this|ProductInterface
     * @throws \Exception
     */
    public function addProductsToExport($profileId, array $productIds)
    {
        $productCollection = $this->_initProducts($productIds);

        if (!$productCollection->getSize()) {
            throw new \Exception(__('Could not add products to export list.'));
        }

        $batch = 100;

        $productCollection->setPageSize($batch);
        $lastPage = $productCollection->getLastPageNumber();
        $currentPage = 1;

        do {
            $productCollection->setCurPage($currentPage);
            $productCollection->load();
            try {
                $this->_saveProducts($profileId, $productCollection);
            } catch (\Exception $e) {
                continue;
            }
            $currentPage++;
            $productCollection->clear();
        } while ($currentPage <= $lastPage);

        $this->_response['success'] = __('Products have been added to export. [IDs %s', implode(', ', $productIds));

        return $this;
    }

    /**
     * @param array $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function _initProducts($productIds = [])
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        if (empty($productIds)) {
            $productCollection->addAttributeToFilter('visibility', [
                'in' => [
                    CatalogProduct\Visibility::VISIBILITY_BOTH,
                    CatalogProduct\Visibility::VISIBILITY_IN_CATALOG,
                    CatalogProduct\Visibility::VISIBILITY_IN_SEARCH,
                ]
            ]);
        } else {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        }

        return $productCollection;
    }

    /**
     * @param $profileId
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     * @throws \Exception
     */
    private function _saveProducts($profileId, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $this->getResource()->saveProducts($profileId, $collection);
        return $this;
    }
}