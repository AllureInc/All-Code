<?php
namespace Mangoit\VendorPayments\Model;

class Paymentfees extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mangoit\VendorPayments\Model\ResourceModel\Paymentfees');
    }
}
?>