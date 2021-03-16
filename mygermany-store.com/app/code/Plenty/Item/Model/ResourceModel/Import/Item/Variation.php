<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item;

use Plenty\Item\Setup\SchemaInterface;
use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\VariationInterface;

/**
 * Class Variation
 * @package Plenty\Item\Model\ResourceModel\Import\Item
 */
class Variation extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, VariationInterface::ENTITY_ID);
    }
}