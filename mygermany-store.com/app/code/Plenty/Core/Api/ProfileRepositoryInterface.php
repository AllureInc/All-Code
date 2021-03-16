<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plenty\Core\Api\Data\ProfileSearchResultsInterface;

/**
 * Interface ProfileRepositoryInterface
 * @package Plenty\Core\Api
 */
interface ProfileRepositoryInterface
{
    /**
     * @param $profileId
     * @return Data\ProfileInterface
     */
    public function get($profileId);

    /**
     * @param $profileId
     * @return Data\ProfileInterface
     */
    public function getById($profileId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProfileSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\ProfileInterface $profile
     * @return mixed
     */
    public function save(Data\ProfileInterface $profile);

    /**
     * @param Data\ProfileInterface $profile
     * @return mixed
     */
    public function delete(Data\ProfileInterface $profile);

    /**
     * @param $profileId
     * @return mixed
     */
    public function deleteById($profileId);
}