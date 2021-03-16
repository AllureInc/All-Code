<?php
namespace Mangoit\VendorPayments\Block\Adminhtml;
class Invoices extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_invoices';/*block grid.php directory*/
        $this->_blockGroup = 'Mangoit_VendorPayments';
        $this->_headerText = __('Invoices');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
        $this->removeButton('add');
		
    }
}
