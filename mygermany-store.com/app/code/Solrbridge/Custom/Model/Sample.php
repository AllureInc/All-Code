<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Model;
//use Magento\Framework\Model\AbstractModel;
class Sample extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init resource model class which use as db connection
     */
    protected function _construct()
    {
        $this->_init('Solrbridge\Custom\Model\ResourceModel\Sample');
    }
}