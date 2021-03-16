<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend ResourceModel.
 */
namespace Cor\MerchandiseHandling\Model\ResourceModel;

class Merchandise extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class Constructor
     */
    protected function _construct() {
        $this->_init('cor_merchandise', 'id');
    }
}