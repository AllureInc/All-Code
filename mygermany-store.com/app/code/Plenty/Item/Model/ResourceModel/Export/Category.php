<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Export;

use Plenty\Item\Model\ResourceModel\ImportExportAbstract;

/**
 * Class Category
 * @package Plenty\Item\Model\ResourceModel\Export
 */
class Category extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init('plenty_item_export_category', 'entity_id');
    }
}