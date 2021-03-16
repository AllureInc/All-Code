<?php
namespace Mangoit\VendorPayments\Model\ResourceModel;

class Vendorinvoices extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mits_vendor_invoices', 'entity_id');
    }
}
?>