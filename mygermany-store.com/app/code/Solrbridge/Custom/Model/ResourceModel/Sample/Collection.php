<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Model\ResourceModel\Sample;
//use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var integer
     */
    protected $_idFieldName = 'sample_id';

    /**
     * Define resourceModel and Model class
     */
    protected function _construct()
    {
        $this->_init('Solrbridge\Custom\Model\Sample', 'Solrbridge\Custom\Model\ResourceModel\Sample');
    }
}