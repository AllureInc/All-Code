<?php
namespace Cor\Eventmanagement\Model\ResourceModel\Event;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Cor\Eventmanagement\Model\Event', 'Cor\Eventmanagement\Model\ResourceModel\Event');
    }
}
