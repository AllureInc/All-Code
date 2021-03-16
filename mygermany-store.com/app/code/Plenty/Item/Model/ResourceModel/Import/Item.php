<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import;

use Plenty\Item\Setup\SchemaInterface;
use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\ItemInterface;

/**
 * Class Item
 * @package Plenty\Item\Model\ResourceModel\Import
 */
class Item extends ImportExportAbstract
{
    /**
     * Resource model initialization
     */
    protected function _construct()
    {
        $this->_init(SchemaInterface::ITEM_IMPORT_ITEM, ItemInterface::ENTITY_ID);
    }
}