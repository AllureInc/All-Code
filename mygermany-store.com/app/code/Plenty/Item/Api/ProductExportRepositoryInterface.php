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

use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Api\Data\Export\CategorySearchResultsInterface;

/**
 * Interface ProductExportRepositoryInterface
 * @package Plenty\Item\Api
 */
interface ProductExportRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CategorySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $entityId
     * @return  ProductInterface
     */
    public function get($entityId);

    /**
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getById($productId);

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku($sku);

    /**
     * @param int $variationId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getByVariationId($variationId);

    /**
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws CouldNotSaveException
     */
    public function save(ProductInterface $product);

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $data, array $fields = []);

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws CouldNotSaveException
     */
    public function update(array $bind, $where = '');

    /**
     * @param ProductInterface $product
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductInterface $product);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);

    /**
     * @param int|null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt($profileId = null);
}
