<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Plenty\Item\Model\Import\ItemRepository;

/**
 * Interface ItemRepositoryInterface
 * @package Plenty\Item\Api
 */
interface ItemRepositoryInterface
{
    /**
     * @param $entityId
     * @param null $field
     * @return Data\Import\ItemInterface
     */
    public function get($entityId, $field = null);

    /**
     * @param $itemId
     * @return Data\Import\ItemInterface
     */
    public function getByItemId($itemId);

    /**
     * @param $variationId
     * @return Data\Import\ItemInterface
     */
    public function getByVariationId($variationId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Import\ItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt($profileId = null);

    /**
     * @param Data\Import\ItemInterface $entity
     * @return Data\Import\ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\Import\ItemInterface $entity);

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
     * @param int $profileId
     * @param Collection $responseData
     * @return mixed
     */
    public function saveItemCollection($profileId, Collection $responseData);

    /**
     * @param Data\Import\ItemInterface $item
     * @return bool
     */
    public function delete(Data\Import\ItemInterface $item);

    /**
     * @param $entityId
     * @return Data\Import\ItemInterface
     */
    public function deleteById($entityId);

}
