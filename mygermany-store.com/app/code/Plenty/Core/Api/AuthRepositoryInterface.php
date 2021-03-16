<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface AuthRepositoryInterface
 * @package Plenty\Core\Api
 */
interface AuthRepositoryInterface
{
    /**
     * @param Data\AuthInterface $auth
     * @return mixed
     */
    public function save(Data\AuthInterface $auth);

    /**
     * @param $auth
     * @return mixed
     */
    public function get($auth);

    /**
     * @param $tokenType
     * @return mixed
     */
    public function getByTokenType($tokenType);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\AuthInterface $auth
     * @return mixed
     */
    public function delete(Data\AuthInterface $auth);

    /**
     * @param $tokenType
     * @return mixed
     */
    public function deleteByTokenType($tokenType);
}