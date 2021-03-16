<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Config\Source;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Config\Source
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\Config\Source::class, \Plenty\Core\Model\ResourceModel\Config\Source::class);
    }

}