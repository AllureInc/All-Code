<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;
use Scommerce\CookiePopup\Model\ResourceModel\Choice\Collection;

/**
 * Class ChoiceRepository
 * @package Scommerce\CookiePopup\Model
 */
class ChoiceRepository implements \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface
{
    /** @var ChoiceInterface[] */
    private $instances = [];

    /** @var \Magento\Framework\Api\DataObjectHelper */
    private $dataObjectHelper;

    /** @var \Scommerce\CookiePopup\Model\ResourceModel\Choice */
    private $resource;

    /** @var \Scommerce\CookiePopup\Model\ChoiceFactory */
    private $choiceFactory;

    /** @var \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterfaceFactory */
    private $searchResultsFactory;

    /** @var \Scommerce\CookiePopup\Model\ResourceModel\Choice\CollectionFactory */
    private $choiceCollectionFactory;

    /**
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Choice $resource
     * @param ChoiceFactory $choiceFactory
     * @param \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Choice\CollectionFactory $choiceCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Scommerce\CookiePopup\Model\ResourceModel\Choice $resource,
        \Scommerce\CookiePopup\Model\ChoiceFactory $choiceFactory,
        \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterfaceFactory $searchResultsFactory,
        \Scommerce\CookiePopup\Model\ResourceModel\Choice\CollectionFactory $choiceCollectionFactory
    ) {
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->resource                 = $resource;
        $this->choiceFactory            = $choiceFactory;
        $this->searchResultsFactory     = $searchResultsFactory;
        $this->choiceCollectionFactory  = $choiceCollectionFactory;
    }

    /**
     * @inheritdoc
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(ChoiceInterface $model)
    {
        try {
            /** @var ChoiceInterface|\Magento\Framework\Model\AbstractModel $model */
            $this->resource->save($model);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the choice: %1', $e->getMessage()));
        }
        return $this->get($model->getId());
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $id = (int) $id;
        if (! $this->_has($id)) {
            $model = $this->_load($id);
            $this->_set($model);
        }
        return $this->_get($id);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->choiceCollectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                $isApplied = $this->applyCustomFilter($collection, $filter);
                if (! $isApplied) {
                    $collection->addFieldToFilter(
                        'main_table.' . $filter->getField(),
                        [$filter->getConditionType() => $filter->getValue()]
                    );
                }
            }
        }
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            /** @var SortOrder $sortOrder */
            $field = $sortOrder->getField();
            $order = $sortOrder->getDirection() == SortOrder::SORT_ASC ? SortOrder::SORT_ASC : SortOrder::SORT_DESC;
            $collection->addOrder($field, $order);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        /** @var \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * @inheritdoc
     * @throws StateException
     */
    public function delete(ChoiceInterface $model)
    {
        /** @var ChoiceInterface|\Magento\Framework\Model\AbstractModel $model */
        $id = (int) $model->getId();
        try {
            $this->resource->delete($model);
            $this->_remove($id);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove choice: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }

    /**
     * Apply custom filters to choice collection
     *
     * @param Collection $collection
     * @param \Magento\Framework\Api\Filter $filter
     * @return bool
     */
    private function applyCustomFilter(Collection $collection, \Magento\Framework\Api\Filter $filter)
    {
        $value = $filter->getValue();
        switch ($filter->getField()) {
            case LinkInterface::CUSTOMER_ID: $collection->addCustomerFilter($value); break;
            case LinkInterface::STORE_ID: $collection->addStoreFilter($value); break;
            case LinkInterface::STATUS: $collection->addStatusFilter($value); break;
            case LinkInterface::CREATED_AT: $collection->addCreatedFilter($value); break;
            case LinkInterface::UPDATED_AT: $collection->addUpdatedFilter($value); break;
            default: return false;
        }

        return true;
    }

    /**
     * Check if instance with id exists
     *
     * @param int $id
     * @return bool
     */
    private function _has($id)
    {
        return isset($this->instances[$id]);
    }

    /**
     * Get instance by id
     *
     * @param int $id
     * @return ChoiceInterface|null
     */
    private function _get($id)
    {
        return $this->_has($id) ? $this->instances[$id] : null;
    }

    /**
     * Set instance
     *
     * @param ChoiceInterface|\Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    private function _set($model)
    {
        $this->instances[(int) $model->getId()] = $model;
        return $this;
    }

    /**
     * Helper method for loading model via one of the field
     *
     * @param mixed $value
     * @param string|null $field
     * @return ChoiceInterface|\Magento\Framework\Model\AbstractModel
     * @throws NoSuchEntityException
     */
    private function _load($value, $field = null)
    {
        $model = $this->choiceFactory->create();
        $this->resource->load($model, $value, $field);
        if (! $model->getId()) {
            throw new NoSuchEntityException(__('Requested feed doesn\'t exist'));
        }
        return $model;
    }

    /**
     * Remove instance by id
     *
     * @param int $id
     */
    private function _remove($id)
    {
        unset($this->instances[$id]);
    }
}
