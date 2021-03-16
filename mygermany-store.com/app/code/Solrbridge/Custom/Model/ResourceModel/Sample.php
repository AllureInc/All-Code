<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Model\ResourceModel;
//use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Sample extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model with tablename, and id field
     */
    protected function _construct()
    {
        $this->_init('solrbridge_custom_sample', 'sample_id');
    }
}