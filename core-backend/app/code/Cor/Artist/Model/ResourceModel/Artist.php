<?php
/**
 * Copyright © 2015 Cor. All rights reserved.
 */
namespace Cor\Artist\Model\ResourceModel;
/**
 * Artist resource
 */
class Artist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cor_artist', 'id');
    }
}
