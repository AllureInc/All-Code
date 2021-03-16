<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Core\Api\ProfileEntityManagementInterface;

/**
 * Interface CategoryImportManagementInterface
 * @package Plenty\Item\Api
 */
interface CategoryImportManagementInterface extends ProfileEntityManagementInterface
{
    const ROOT_CATEGORY_MAPPING = 'root_category_mapping';
    const DEFAULT_STORE_ID = 'default_store_id';

    /**
     * @param string $defaultLang
     * @param array $categoryIds
     * @return $this|CategoryImportManagementInterface
     * @throws \Exception
     */
    public function execute(
        $defaultLang,
        array $categoryIds = []
    );
}