<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Item\Api\Data\Export\CategoryInterface;
use Plenty\Item\Api\Data\Export\CategorySearchResultsInterface;

/**
 * Interface CategoryExportRepositoryInterface
 * @package Plenty\Item\Api
 */
interface CategoryExportRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CategorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $entityId
     * @return  CategoryInterface
     */
    public function get($entityId);

    /**
     * @param $magentoCategoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById($magentoCategoryId);

    /**
     * @param $plentyCategoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByPlentyId($plentyCategoryId);

    /**
     * @param $path
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByPath($path);

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
    );

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
