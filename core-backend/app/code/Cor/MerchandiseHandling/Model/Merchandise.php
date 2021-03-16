<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Model.
 */
namespace Cor\MerchandiseHandling\Model;

class Merchandise extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class Constructor
     */
    protected function _construct() {
        $this->_init('Cor\MerchandiseHandling\Model\ResourceModel\Merchandise');
    }
}