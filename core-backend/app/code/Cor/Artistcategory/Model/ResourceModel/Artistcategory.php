<?php
/**
 * Copyright © 2015 Cor. All rights reserved.
 */
namespace Cor\Artistcategory\Model\ResourceModel;

/**
 * Artistcategory resource
 */
class Artistcategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cor_artistcategory', 'id');
    }  
}
