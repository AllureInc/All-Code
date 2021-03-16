<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item;

use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\MediaInterface;
use Plenty\Item\Setup\SchemaInterface;

/**
 * Class Media
 * @package Plenty\Item\Model\ResourceModel\Import\Item
 */
class Media extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, MediaInterface::ENTITY_ID);
    }

    /**
     * @param $itemId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function lookupItemMediaRecords($itemId)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'media_id')
            ->where('item_id = :item_id');
        $binds = [':item_id' => (int) $itemId];
        return $adapter->fetchCol($select, $binds);
    }
}