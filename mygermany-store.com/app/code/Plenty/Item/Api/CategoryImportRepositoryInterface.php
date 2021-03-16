<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Item\Api\Data\Import\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface CategoryImportRepositoryInterface
 * @package Plenty\Item\Api
 */
interface CategoryImportRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Import\CategorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $entityId
     * @return  Data\Import\CategoryInterface
     */
    public function get($entityId);

    /**
     * @param $plentyCategoryId
     * @return Data\Import\CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById($plentyCategoryId);

    /**
     * @param $magentoCategoryId
     * @return Data\Import\CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByMagentoId($magentoCategoryId);

    /**
     * @param $path
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByPath($path);

    /**
     * @param CategoryInterface $category
     * @return CategoryInterface
     * @throws CouldNotSaveException
     */
    public function save(CategoryInterface $category);

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $data, array $fields = []);

     /**
      * @param CategoryInterface $category
      * @return bool
      * @throws CouldNotDeleteException
      */
    public function delete(CategoryInterface $category);

    /**
     * @param $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt($profileId = null);
}