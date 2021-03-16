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
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller as ResourcePreorderSeller;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PreorderSellerRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreorderSellerRepository implements PreorderSellerRepositoryInterface
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
    protected $dataPreorderSellerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourcePreorderSeller $resource
     * @param PreorderSellerFactory $preorderSellerFactory
     * @param Data\PreorderItemsInterfaceFactory $dataPreorderSellerFactory
     * @param PreorderSellerCollectionFactory $preorderCollectionFactory
     * @param Data\PreorderSellerSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePreorderSeller $resource,
        PreorderSellerFactory $preorderSellerFactory,
        \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterfaceFactory $dataPreorderSellerFactory,
        PreorderSellerCollectionFactory $preorderCollectionFactory,
        Data\PreorderSellerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->preorderSellerFactory = $preorderSellerFactory;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPreorderSellerFactory = $dataPreorderSellerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Preorder Seller data
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $preorderItems
     * @return PreorderSeller
     * @throws CouldNotSaveException
     */
    public function save(Data\PreorderSellerInterface $preorderSeller)
    {

        try {
            $this->resource->save($preorderSeller);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $preorderSeller;
    }

    /**
     * Load Preorder Seller data by given Identity
     *
     * @param string $id
     * @return PreorderSeller
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $preorderSeller = $this->preorderSellerFactory->create();
        $this->resource->load($preorderSeller, $id);
        if (!$preorderSeller->getId()) {
            throw new NoSuchEntityException(__('Preorder Items with id "%1" does not exist.', $id));
        }
        return $preorderSeller;
    }

    /**
     * Load PreorderSeller data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\Collection
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
        $preorderSeller = [];
        /** @var preorderSeller $preorderSellerModel */
        foreach ($collection as $preorderSellerModel) {
            $preorderSellerData = $this->dataPreorderSellerFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $preorderSellerData,
                $preorderSellerModel->getData(),
                'Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface'
            );
            $preorderSeller[] = $this->dataObjectProcessor->buildOutputDataArray(
                $preorderSellerData,
                'Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface'
            );
        }
        $searchResults->setItems($preorderSeller);
        return $searchResults;
    }

    /**
     * Delete PreorderSeller
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $preorderSeller
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\PreorderSellerInterface $preorderSeller)
    {
        try {
            $this->resource->delete($preorderSeller);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderSeller by given Identity
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
