<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model;

use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Core\Api\Data;
use Plenty\Core\Model\ResourceModel\Profile as ResourceProfile;
use Plenty\Core\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProfileRepository
 * @package Plenty\Core\Model
 */
class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var ResourceProfile
     */
    protected $resource;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var ProfileCollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @var Data\ProfileSearchResultsInterface|Data\ProfileSearchResultsInterfaceFactory
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
     * @var Data\ProfileInterfaceFactory
     */
    protected $dataProfileFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;


    /**
     * ProfileRepository constructor.
     * @param ResourceProfile $resource
     * @param ProfileFactory $profileFactory
     * @param Data\ProfileInterfaceFactory $dataProfileFactory
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param Data\ProfileSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceProfile $resource,
        ProfileFactory $profileFactory,
        Data\ProfileInterfaceFactory $dataProfileFactory,
        ProfileCollectionFactory $profileCollectionFactory,
        Data\ProfileSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProfileFactory = $dataProfileFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function get($profile)
    {

    }

    /**
     * @param Data\ProfileInterface $profile
     * @return mixed|Data\ProfileInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(Data\ProfileInterface $profile)
    {
        try {
            $this->resource->save($profile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $profile;
    }

    /**
     * @param $profileId
     * @return null|Profile
     * @throws NoSuchEntityException
     */
    public function getById($profileId)
    {
        $profile = $this->profileFactory->create();
        $this->resource->load($profile, $profileId);
        if (!$profile->getId()) {
            throw new NoSuchEntityException(__('Profile with id "%1" does not exist.', $profileId));
        }
        return $profile;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return Data\ProfileSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var \Plenty\Core\Model\ResourceModel\Profile\Collection $collection */
        $collection = $this->profileCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param Data\ProfileInterface $profile
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProfileInterface $profile)
    {
        try {
            $this->resource->delete($profile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $profileId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($profileId)
    {
        return $this->delete($this->getById($profileId));
    }
}
