<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Core\Api\ProfileEntityManagementInterface;

/**
 * Interface CategoryCollectManagementInterface
 * @package Plenty\Item\Api
 */
interface CategoryCollectManagementInterface extends ProfileEntityManagementInterface
{
    const ROOT_CATEGORY_MAPPING = 'root_category_mapping';
    const DEFAULT_STORE_ID = 'default_store_id';

    /**
     * @return mixed
     */
    public function getRootCategoryMapping();

    /**
     * @param array $categories
     * @return $this
     */
    public function setRootCategoryMapping(array $categories);

    /**
     * @return null|int
     */
    public function getDefaultStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setDefaultStoreId(int $storeId);

    /**
     * @param null $categoryId
     * @param null $updatedAt
     * @param null $with
     * @param null $type
     * @param null $lang
     * @param null $parentId
     * @param null $name
     * @param null $level
     * @return $this
     * @throws \Exception
     */
    public function execute(
        $categoryId = null,
        $updatedAt = null,
        $with = null,
        $type = null,
        $lang = null,
        $parentId = null,
        $name = null,
        $level = null
    );
}