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
use Webkul\MarketplacePreorder\Api\PreorderItemsRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems as ResourcePreorderItems;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PreorderItemsRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreorderItemsRepository implements PreorderItemsRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $preorderItemsFactory;

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
    protected $dataPreorderItemsFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourcePreorderItems $resource
     * @param PreorderItemsFactory $preorderItemsFactory
     * @param Data\PreorderItemsInterfaceFactory $dataPreorderItemsFactory
     * @param PreorderItemsCollectionFactory $preorderCollectionFactory
     * @param Data\PreorderItemsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePreorderItems $resource,
        PreorderItemsFactory $preorderItemsFactory,
        \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory $dataPreorderItemsFactory,
        PreorderItemsCollectionFactory $preorderCollectionFactory,
        Data\PreorderItemsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->preorderItemsFactory = $preorderItemsFactory;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPreorderItemsFactory = $dataPreorderItemsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Preorder Items data
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface $preorderItems
     * @return PreorderItems
     * @throws CouldNotSaveException
     */
    public function save(Data\PreorderItemsInterface $preorderItems)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $preorderItems->setStoreId($storeId);
        try {
            $this->resource->save($preorderItems);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $preorderItems;
    }

    /**
     * Load Preorder Items data by given Block Identity
     *
     * @param string $id
     * @return PreorderItems
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $preorderItems = $this->preorderItemsFactory->create()->load($id);

        if (!$preorderItems->getId()) {
            throw new NoSuchEntityException(__('Preorder Items with id "%1" does not exist.', $id));
        }
        return $preorderItems;
    }

    /**
     * Load PreorderItems data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\Collection
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
        $sortOrdersData = $criteria->getSortOrders();
        if ($sortOrdersData) {
            foreach ($sortOrdersData as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());

        $collection->setPageSize($criteria->getPageSize());
        
        $preorderItem = [];
        /** @var PreorderItem $preorderItemModel */
        foreach ($collection as $preorderItemModel) {
            $preorderItemData = $this->dataPreorderItemsFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $preorderItemData,
                $preorderItemModel->getData(),
                'Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
            );
            $preorderItem[] = $this->dataObjectProcessor->buildOutputDataArray(
                $preorderItemData,
                'Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
            );
        }
        $searchResults->setItems($preorderItem);
        return $searchResults;
    }

    /**
     * Delete PreorderItems
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface $preorderItems
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\PreorderItemsInterface $preorderItems)
    {
        try {
            $this->resource->delete($preorderItems);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderItems by given Block Identity
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
