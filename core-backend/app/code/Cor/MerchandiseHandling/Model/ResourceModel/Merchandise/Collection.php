<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Collection.
 * returns collection of 'cor_merchandise' table.
 */
namespace Cor\MerchandiseHandling\Model\ResourceModel\Merchandise;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Class Constructor
     */
    protected function _construct() {
        $this->_init('Cor\MerchandiseHandling\Model\Merchandise', 'Cor\MerchandiseHandling\Model\ResourceModel\Merchandise');
    }
}
