<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

use Plenty\Stock\Api\StockImportRepositoryInterface;
use Plenty\Stock\Model\ResourceModel;
use Plenty\Stock\Api\Data\Import\InventoryInterface;
use Plenty\Stock\Api\Data\Import\InventorySearchResultsInterface;
use Plenty\Stock\Api\Data\Import\InventorySearchResultsInterfaceFactory;
use Plenty\Stock\Helper;

/**
 * Class ItemRepository
 * @package Plenty\Item\Model\Import
 */
class StockImportRepository implements StockImportRepositoryInterface
{
    /**
     * @var ResourceModel\Import\Inventory
     */
    private $_resource;

    /**
     * @var Import\InventoryFactory
     */
    private $_inventoryFactory;

    /**
     * @var ResourceModel\Import\Inventory\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var InventorySearchResultsInterfaceFactory
     */
    private $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * @var Helper\Data
     */
    private $_helper;

    /**
     * @var Json
     */
    private $_dateTime;

    /**
     * @var Json|null
     */
    private $_serializer;

    /**
     * StockImportRepository constructor.
     * @param ResourceModel\Import\Inventory $resource
     * @param Import\InventoryFactory $inventoryFactory
     * @param ResourceModel\Import\Inventory\CollectionFactory $collectionFactory
     * @param InventorySearchResultsInterfaceFactory $searchResultsFactory
     * @param Helper\Data $helper
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Import\Inventory $resource,
        Import\InventoryFactory $inventoryFactory,
        ResourceModel\Import\Inventory\CollectionFactory $collectionFactory,
        InventorySearchResultsInterfaceFactory $searchResultsFactory,
        Helper\Data $helper,
        DateTime $dateTime,
        ?Json $serializer = null,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_inventoryFactory = $inventoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_serializer = $serializer
            ?: ObjectManager::getInstance()->get(Json::class);
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return InventorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Import\Inventory\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var InventorySearchResultsInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param $entityId
     * @param null $field
     * @return InventoryInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var InventoryInterface $inventory */
        $inventory = $this->_inventoryFactory->create();
        $this->_resource->load($inventory, $entityId, $field);
        if (!$inventory->getId()) {
            throw new NoSuchEntityException(__('The item with ID "%1" doesn\'t exist.', $entityId));
        }
        return $inventory;
    }

    /**
     * @param $sku
     * @return InventoryInterface
     * @throws NoSuchEntityException
     */
    public function getBySku($sku)
    {
        return $this->get($sku, InventoryInterface::ITEM_ID);
    }

    /**
     * @param $variationId
     * @return InventoryInterface
     * @throws NoSuchEntityException
     */
    public function getByVariationId($variationId)
    {
        return $this->get($variationId, InventoryInterface::VARIATION_ID);
    }

    /**
     * @param InventoryInterface $inventory
     * @return InventoryInterface
     * @throws CouldNotSaveException
     */
    public function save(InventoryInterface $inventory)
    {
        try {
            $this->_resource->save($inventory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $inventory;
    }

    /**
     * @param array $stockData
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function saveMultiple(array $stockData, array $fields = [])
    {
        try {
            $this->_resource->addMultiple($stockData, $fields);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param InventoryInterface $inventory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InventoryInterface $inventory)
    {
        try {
            $this->_resource->delete($inventory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $entityId
     * @return bool|InventoryInterface
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

        /** @var ResourceModel\Import\Inventory\Collection $collection */
        $collection = $this->_collectionFactory->create();
        if (null !== $profileId) {
            $collection->addProfileFilter($profileId);
        }
        $collection->addFieldToSelect('collected_at')
            ->setOrder('collected_at', 'desc')
            ->setPageSize(1);

        /** @var InventoryInterface $inventory */
        $inventory = $collection->getFirstItem();

        if (strtotime($inventory->getCollectedAt()) > 0) {
            $lastUpdatedAt = $this->_helper->getDateTimeLocale($inventory->getCollectedAt());
        }

        return $lastUpdatedAt;
    }
}
