<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Attribute;

use Plenty\Item\Api\AttributeImportRepositoryInterface;
use Plenty\Item\Api\Data\Import\AttributeInterface;
use Plenty\Item\Model\Import\AttributeFactory;
use Plenty\Item\Model\ResourceModel\Import\Attribute\Collection;
use Plenty\Item\Model\ResourceModel\Import\Attribute\CollectionFactory;
use Plenty\Item\Api\Data\AttributeImportSearchResultsInterface;
use Plenty\Item\Api\Data\AttributeImportSearchResultsInterfaceFactory;
use Plenty\Item\Model\ResourceModel\Import\Attribute as AttributeResourceModel;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AttributeRepository
 * @package Plenty\Item\Model\Import
 */
class Repository implements AttributeImportRepositoryInterface
{
    /**
     * @var AttributeResourceModel
     */
    protected $_resource;

    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var AttributeImportSearchResultsInterfaceFactory|AttributeImportSearchResultsInterface
     */
    protected $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * Repository constructor.
     * @param AttributeResourceModel $resource
     * @param AttributeFactory $attributeFactory
     * @param CollectionFactory $collectionFactory
     * @param AttributeImportSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        AttributeResourceModel $resource,
        AttributeFactory $attributeFactory,
        CollectionFactory $collectionFactory,
        AttributeImportSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_attributeFactory = $attributeFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor;
    }

    /**
     * @param $attributeCode
     * @param string $type
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function get($attributeCode, $type = 'attribute_code')
    {
        $attributeModel = $this->_attributeFactory->create();
        $this->_resource->load($attributeModel, $attributeCode, $type);
        if (!$attributeModel->getId()) {
            throw new NoSuchEntityException(__('Attribute code "%1" does not exist.', $attributeCode));
        }
        return $attributeModel;
    }

    /**
     * @param AttributeInterface $attribute
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface|AttributeInterface
     * @throws CouldNotSaveException
     */
    public function save(AttributeInterface $attribute)
    {
        try {
            $this->_resource->save($attribute);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $attribute;
    }

    /**
     * @param $attributeId
     * @param string $type
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($attributeId, $type = 'attribute_id')
    {
        $attributeModel = $this->_attributeFactory->create();
        $this->_resource->load($attributeModel, $attributeId, $type);
        if (!$attributeModel->getId()) {
            throw new NoSuchEntityException(__('Attribute with id "%1" does not exist.', $attributeId));
        }
        return $attributeModel;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return AttributeImportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($criteria, $collection);

        /** @var AttributeImportSearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(AttributeInterface $attribute)
    {
        try {
            $this->_resource->delete($attribute);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $attribute
     * @param string $type
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($attribute, $type = 'attribute_id')
    {
        return $this->delete($this->getById($attribute, $type));
    }
}
