<?php
namespace Mangoit\VendorPayments\Model\ResourceModel;

class Exchangefees extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mits_currency_exchange_charge', 'entity_id');
    }
}
?>