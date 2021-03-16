<?php
namespace Mangoit\VendorPayments\Block\Adminhtml\Invoices\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_invoices_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Invoices Information'));
    }
}