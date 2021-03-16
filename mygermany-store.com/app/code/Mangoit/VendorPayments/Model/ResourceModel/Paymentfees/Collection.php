<?php

namespace Mangoit\VendorPayments\Model\ResourceModel\Paymentfees;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mangoit\VendorPayments\Model\Paymentfees', 'Mangoit\VendorPayments\Model\ResourceModel\Paymentfees');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>