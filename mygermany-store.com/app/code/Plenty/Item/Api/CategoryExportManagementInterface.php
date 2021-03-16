<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Core\Api\ProfileEntityManagementInterface;

/**
 * Interface CategoryExportManagementInterface
 * @package Plenty\Item\Api
 */
interface CategoryExportManagementInterface extends ProfileEntityManagementInterface
{
    const ROOT_CATEGORY_MAPPING = 'root_category_mapping';
    const DEFAULT_STORE_ID = 'default_store_id';
    const DEFAULT_LANG = 'default_lang';

    /**
     * @return null|int
     * @throws \Exception
     */
    public function getDefaultLang();

    /**
     * @param string $lang
     * @return $this
     */
    public function setDefaultLang(string $lang);

    /**
     * @return null|array
     */
    public function getRootCategoryMapping();

    /**
     * @param array $categories
     * @return $this
     */
    public function setRootCategoryMapping(array $categories);

    /**
     * @param array $categoryIds
     * @return $this|CategoryImportManagementInterface
     * @throws \Exception
     */
    public function export(
        array $categoryIds = []
    );

    public function addToExport(array $categoryIds);
}