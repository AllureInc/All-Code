<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item;

use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\PropertyInterface;
use Plenty\Item\Setup\SchemaInterface;

/**
 * Class Property
 * @package Plenty\Item\Model\ResourceModel\Import\Item
 */
class Property extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, PropertyInterface::ENTITY_ID);
    }
}