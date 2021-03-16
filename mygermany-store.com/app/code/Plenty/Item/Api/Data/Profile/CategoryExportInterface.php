<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Profile;

/**
 * Interface CategoryExportInterface
 * @package Plenty\Item\Api\Data\Profile
 */
interface CategoryExportInterface
{
    // Stages
    const STAGE_EXPORT_CATEGORY = 'export_category';

    // Category config scope
    const IS_ACTIVE_CATEGORY_EXPORT = 'category_export/category_config/is_active_category_export';
    const ROOT_CATEGORY_MAPPING = 'category_export/category_config/root_category_mapping';

    /**
     * @return bool
     */
    public function getIsActiveCategoryExport();
}
