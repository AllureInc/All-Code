<?php

namespace Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
                'Mangoit\VendorPayments\Model\Vendorinvoices',
                'Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices'
            );
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>