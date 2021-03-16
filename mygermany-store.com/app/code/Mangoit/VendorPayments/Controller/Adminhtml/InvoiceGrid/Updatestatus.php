<?php
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;

use Magento\Framework\View\Result\PageFactory;

class Updatestatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->invoiceModel = $invoiceModel;
        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            if(isset($data['id'])) {
                $invoiceMod = $this->invoiceModel->load($data['id']);
                if($invoiceMod->getId()) {
                    $invoiceMod->setInvoiceStatus($data['invoice_status'])->save();
                    $this->messageManager->addSuccess(
                        __('Invoice status has been changed.')
                    );
                } else {
                    $this->messageManager->addError(__('No invoice found.'));
                }
            } else {
                $this->messageManager->addError(__('Wrong URL path.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
        // echo "<pre>";
        // print_r($data);
        // die;
    }
}
