<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Export;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Api\ProductExportRepositoryInterface;
use Plenty\Item\Api\Data\Export\ProductSearchResultsInterface;
use Plenty\Item\Api\Data\Export\ProductSearchResultsInterfaceFactory;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Helper;

/**
 * Class ProductRepository
 * @package Plenty\Item\Model\Export
 */
class ProductRepository implements ProductExportRepositoryInterface
{
    /**
     * @var ResourceModel\Export\Category
     */
    protected $_resource;

    /**
     * @var CategoryFactory
     */
    private $_productFactory;

    /**
     * @var ResourceModel\Export\Category\CollectionFactory
     */
    private $_collectionFactory;


    private $_searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * @var Helper\Data
     */
    private $_helper;

    /**
     * ProductRepository constructor.
     * @param ResourceModel\Export\Product $resource
     * @param ProductFactory $categoryFactory
     * @param ResourceModel\Export\Product\CollectionFactory $collectionFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param Helper\Data $helper
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Export\Product $resource,
        ProductFactory $categoryFactory,
        ResourceModel\Export\Product\CollectionFactory $collectionFactory,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        Helper\Data $helper,
        ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_productFactory = $categoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultFactory = $searchResultsFactory;
        $this->_helper = $helper;
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return ProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var ResourceModel\Import\Category\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($criteria, $collection);

        /** @var ProductSearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param int $entityId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId)
    {
        /** @var ProductInterface $product */
        $product = $this->_productFactory->create();
        $this->_resource->load($product, $entityId);
        if (!$product->getId()) {
            throw new NoSuchEntityException(__('The product with ID "%1" doesn\'t exist.', $entityId));
        }
        return $product;
    }

    /**
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getById($productId)
    {
        /** @var ProductInterface $product */
        $product = $this->_productFactory->create();
        $this->_resource->load($product, $productId, ProductInterface::PRODUCT_ID);
        if (!$product->getId()) {
            throw new NoSuchEntityException(
                __('The product with ID "%1" doesn\'t exist.', $productId)
            );
        }
        return $product;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku($sku)
    {
        /** @var ProductInterface $product */
        $product = $this->_productFactory->create();
        $this->_resource->load($product, $sku, ProductInterface::SKU);
        if (!$product->getId()) {
            throw new NoSuchEntityException(
                __('The product with SKU "%1" doesn\'t exist.', $sku)
            );
        }
        return $product;
    }

    /**
     * @param int $variationId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getByVariationId($variationId)
    {
        /** @var ProductInterface $product */
        $product = $this->_productFactory->create();
        $this->_resource->load($product, $variationId, ProductInterface::VARIATION_ID);
        if (!$product->getId()) {
            throw new NoSuchEntityException(
                __('The product with variation ID "%1" doesn\'t exist.', $variationId)
            );
        }
        return $product;
    }

    /**
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws CouldNotSaveException
     */
    public function save(ProductInterface $product)
    {
        try {
            $this->_resource->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $product;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $data, array $fields = [])
    {
        try {
            $this->_resource->addMultiple($data, $fields);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws CouldNotSaveException
     */
    public function update(array $bind, $where = '')
    {
        try {
            $this->_resource->update($bind, $where);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductInterface $product)
    {
        try {
            $this->_resource->delete($product);
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
    public function getLastUpdatedAt($profileId = null)
    {
        $lastUpdatedAt = null;

        /** @var ResourceModel\Import\Category\Collection $collection */
        $collection = $this->_collectionFactory->create();
        if (null !== $profileId) {
            $collection->addProfileFilter($profileId);
        }

        $collection->addFieldToSelect(ProductInterface::UPDATED_AT)
            ->setOrder(ProductInterface::UPDATED_AT, 'desc')
            ->setPageSize(1);

        /** @var ProductInterface $product */
        $product = $collection->getFirstItem();

        if (strtotime($product->getUpdatedAt()) > 0) {
            $lastUpdatedAt = $this->_helper->getDateTimeLocale($product->getUpdatedAt());
        }

        return $lastUpdatedAt;
    }
}
