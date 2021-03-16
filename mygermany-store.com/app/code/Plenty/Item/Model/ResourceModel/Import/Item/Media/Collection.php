<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item\Media;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Item\Media
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\Import\Item\Media::class, \Plenty\Item\Model\ResourceModel\Import\Item\Media::class);
    }

    /**
     * @return $this
     */
    public function addUniqueFilter()
    {
        $this->getSelect()->group('md5_checksum');
        return $this;
    }
}