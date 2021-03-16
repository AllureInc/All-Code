<?php
namespace Cor\Eventmanagement\Model\ResourceModel;

/**
 * Event resource
 */
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cor_events', 'id');
    }
}
