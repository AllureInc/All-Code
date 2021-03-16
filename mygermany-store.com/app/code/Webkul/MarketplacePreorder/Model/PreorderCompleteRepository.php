<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Model;

use Webkul\MarketplacePreorder\Api\Data;
use Webkul\MarketplacePreorder\Api\PreorderCompleteRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete as ResourcePreorderComplete;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\CollectionFactory as
PreorderCompleteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PreorderCompleteRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreorderCompleteRepository implements PreorderCompleteRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $preorderCompleteFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $preorderCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Cms\Api\Data\BlockInterfaceFactory
     */
    protected $dataPreorderCompleteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourcePreorderComplete $resource
     * @param PreorderCompleteFactory $preorderCompleteFactory
     * @param Data\PreorderCompleteInterfaceFactory $dataPreorderCompleteFactory
     * @param PreorderCompleteCollectionFactory $preorderCollectionFactory
     * @param Data\PreorderCompleteSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePreorderComplete $resource,
        PreorderCompleteFactory $preorderCompleteFactory,
        \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterfaceFactory $dataPreorderCompleteFactory,
        PreorderCompleteCollectionFactory $preorderCollectionFactory,
        Data\PreorderCompleteSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->preorderCompleteFactory = $preorderCompleteFactory;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPreorderCompleteFactory = $dataPreorderCompleteFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Preorder Complete data
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface $preorderComplete
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\PreorderCompleteInterface $preorderComplete)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $preorderComplete->setStoreId($storeId);
        try {
            $this->resource->save($preorderComplete);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $preorderComplete;
    }

    /**
     * Load Preorder Complete data by given Block Identity
     *
     * @param string $id
     * @return PreorderComplete
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $preorderComplete = $this->preorderCompleteFactory->create();
        $this->resource->load($preorderComplete, $id);
        if (!$preorderComplete->getId()) {
            throw new NoSuchEntityException(__('Preorder Items with id "%1" does not exist.', $id));
        }
        return $preorderComplete;
    }

    /**
     * Load PreorderComplete data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->preorderCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $preorderCompleteData = [];
        /** @var PreorderComplete $preorderCompleteModel */
        foreach ($collection as $preorderCompleteModel) {
            $preorderComplete = $this->dataPreorderCompleteFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $preorderComplete,
                $preorderCompleteModel->getData(),
                'Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface'
            );
            $preorderCompleteData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $preorderComplete,
                'Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface'
            );
        }
        $searchResults->setItems($preorderCompleteData);
        return $searchResults;
    }

    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface $preorderComplete
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\PreorderCompleteInterface $preorderComplete)
    {
        try {
            $this->resource->delete($preorderComplete);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderComplete by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
