<?php
namespace Cor\Eventmanagement\Model\ResourceModel\Eventartist;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Cor\Eventmanagement\Model\Eventartist', 'Cor\Eventmanagement\Model\ResourceModel\Eventartist');
    }
}
