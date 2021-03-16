<?php
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;
use Magento\Backend\App\Action;
class NewAction extends \Magento\Backend\App\Action
{
     public function execute()
    {
		$this->_forward('edit');
    }
}
