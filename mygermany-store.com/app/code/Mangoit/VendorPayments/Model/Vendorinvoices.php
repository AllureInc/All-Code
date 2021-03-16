<?php
namespace Mangoit\VendorPayments\Model;

class Vendorinvoices extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices');
    }
}
?>