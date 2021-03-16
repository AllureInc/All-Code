<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Export\Category;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Export\Category
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\Export\Category::class, \Plenty\Item\Model\ResourceModel\Export\Category::class);
    }

}