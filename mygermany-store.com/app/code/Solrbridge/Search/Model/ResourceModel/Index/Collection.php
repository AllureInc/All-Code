<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\ResourceModel\Index;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Solrbridge\Search\Model\Index', 'Solrbridge\Search\Model\ResourceModel\Index');
    }
}
