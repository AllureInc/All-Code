<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Export;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Item\Api\Data\Export\CategoryInterface;
use Plenty\Item\Api\CategoryExportRepositoryInterface;
use Plenty\Item\Api\Data\Export\CategorySearchResultsInterface;
use Plenty\Item\Api\Data\Export\CategorySearchResultsInterfaceFactory;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Helper;

/**
 * Class CategoryRepository
 * @package Plenty\Item\Model\Export
 */
class CategoryRepository implements CategoryExportRepositoryInterface
{
    /**
     * @var ResourceModel\Export\Category
     */
    protected $_resource;

    /**
     * @var CategoryFactory
     */
    private $_categoryFactory;

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
     * CategoryRepository constructor.
     * @param ResourceModel\Export\Category $resource
     * @param CategoryFactory $categoryFactory
     * @param ResourceModel\Export\Category\CollectionFactory $collectionFactory
     * @param CategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param Helper\Data $helper
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Export\Category $resource,
        CategoryFactory $categoryFactory,
        ResourceModel\Export\Category\CollectionFactory $collectionFactory,
        CategorySearchResultsInterfaceFactory $searchResultsFactory,
        Helper\Data $helper,
        ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_categoryFactory = $categoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultFactory = $searchResultsFactory;
        $this->_helper = $helper;
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return CategorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var ResourceModel\Import\Category\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($criteria, $collection);

        /** @var CategorySearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param $entityId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId)
    {
        /** @var CategoryInterface $category */
        $category = $this->_categoryFactory->create();
        $this->_resource->load($category, $entityId);
        if (!$category->getId()) {
            throw new NoSuchEntityException(__('The category with ID "%1" doesn\'t exist.', $entityId));
        }
        return $category;
    }

    /**
     * @param $plentyCategoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById($plentyCategoryId)
    {
        /** @var CategoryInterface $category */
        $category = $this->_categoryFactory->create();
        $this->_resource->load($category, $plentyCategoryId, CategoryInterface::CATEGORY_ID);
        if (!$category->getId()) {
            throw new NoSuchEntityException(
                __('The category with Magento ID "%1" doesn\'t exist.', $plentyCategoryId)
            );
        }
        return $category;
    }

    /**
     * @param $magentoCategoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByPlentyId($magentoCategoryId)
    {
        /** @var CategoryInterface $category */
        $category = $this->_categoryFactory->create();
        $this->_resource->load($category, $magentoCategoryId, CategoryInterface::PLENTY_CATEGORY_ID);
        if (!$category->getId()) {
            throw new NoSuchEntityException(
                __('The category with Magento Category ID "%1" doesn\'t exist.', $magentoCategoryId)
            );
        }
        return $category;
    }

    /**
     * @param $path
     * @param string $pathType
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByPath($path, $pathType = CategoryInterface::CATEGORY_PATH)
    {
        /** @var CategoryInterface $category */
        $category = $this->_categoryFactory->create();
        $this->_resource->load($category, $path, CategoryInterface::CATEGORY_PATH);
        if (!$category->getId()) {
            throw new NoSuchEntityException(
                __('The category with path "%1" doesn\'t exist.', $path)
            );
        }
        return $category;
    }

    /**
     * @param $categoryId
     * @param string $entityType
     * @param string $returnColumnName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPathByCategoryId(
        $categoryId,
        string $entityType = CategoryInterface::CATEGORY_ID,
        $returnColumnName = CategoryInterface::CATEGORY_PATH
    ) {
        $adapter = $this->_resource->getConnection();
        $select = $adapter->select()
            ->from($this->_resource->getMainTable(), [$returnColumnName])
            ->where($entityType.' = ?', $categoryId);
        $result = $adapter->fetchOne($select);
        return $result;
    }

    /**
     * @param CategoryInterface $category
     * @return CategoryInterface
     * @throws CouldNotSaveException
     */
    public function save(CategoryInterface $category)
    {
        try {
            $this->_resource->save($category);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $category;
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
     * @param CategoryInterface $category
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CategoryInterface $category)
    {
        try {
            $this->_resource->delete($category);
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

        $collection->addFieldToSelect(CategoryInterface::UPDATED_AT)
            ->setOrder(CategoryInterface::UPDATED_AT, 'desc')
            ->setPageSize(1);

        /** @var CategoryInterface $category */
        $category = $collection->getFirstItem();

        if (strtotime($category->getUpdatedAt()) > 0) {
            $lastUpdatedAt = $this->_helper->getDateTimeLocale($category->getUpdatedAt());
        }

        return $lastUpdatedAt;
    }
}
