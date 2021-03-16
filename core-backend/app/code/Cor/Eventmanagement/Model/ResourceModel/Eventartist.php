<?php
/**
 * Copyright © 2015 Cor. All rights reserved.
 */
namespace Cor\Eventmanagement\Model\ResourceModel;

/**
 * Event resource
 */
class Eventartist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cor_event_artist', 'id');
    }
}
