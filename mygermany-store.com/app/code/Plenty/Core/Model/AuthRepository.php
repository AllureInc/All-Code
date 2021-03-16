<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model;

use Plenty\Core\Api\AuthRepositoryInterface;
use \Plenty\Core\Api\Data;
use Plenty\Core\Model\ResourceModel\Auth as ResourceAuth;
use Plenty\Core\Model\ResourceModel\Auth\CollectionFactory as AuthCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BlockRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @var ResourceProfile
     */
    protected $_resource;

    /**
     * @var ProfileFactory
     */
    protected $_authFactory;

    /**
     * @var ProfileCollectionFactory
     */
    protected $_authCollectionFactory;

    /**
     * @var Data\ProfileSearchResultsInterface|Data\ProfileSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;



    /**
     * @var Data\ProfileInterfaceFactory
     */
    protected $dataProfileFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;


    /**
     * AuthRepository constructor.
     * @param ResourceAuth $resource
     * @param ProfileFactory $authFactory
     * @param AuthCollectionFactory $profileCollectionFactory
     * @param Data\AuthSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceAuth $resource,
        ProfileFactory $authFactory,
        AuthCollectionFactory $profileCollectionFactory,
        Data\AuthSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_authFactory = $authFactory;
        $this->_authCollectionFactory = $profileCollectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor;
    }

    public function get($auth)
    {

    }

    /**
     * @param Data\AuthInterface $auth
     * @return mixed|Data\AuthInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\AuthInterface $auth)
    {
        try {
            $this->_resource->save($auth);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $auth;
    }

    /**
     * @param $tokenType
     * @return mixed|Profile
     * @throws NoSuchEntityException
     */
    public function getByTokenType($tokenType)
    {
        $auth = $this->_authFactory->create();
        $this->_resource->load($auth, $tokenType, 'token_type');
        if (!$auth->getId()) {
            throw new NoSuchEntityException(__('Auth with token type "%s" does not exist.', $tokenType));
        }
        return $auth;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed|Data\ProfileSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->_authCollectionFactory->create();

        $this->_collectionProcessor->process($criteria, $collection);

        /** @var Data\ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param Data\AuthInterface $auth
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(Data\AuthInterface $auth)
    {
        try {
            $this->_resource->delete($auth);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $tokenType
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteByTokenType($tokenType)
    {
        return $this->delete($this->getByTokenType($tokenType));
    }
}
