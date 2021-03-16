<?php
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryObject;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Registry $registryObject
    ) {
        $this->invoiceModel = $invoiceModel;
        $this->registryObject = $registryObject;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        $model = $this->invoiceModel;

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $resultPage = $this->pageFactory->create();
        if($model->getInvoiceTyp() == 1){
            $resultPage->addHandle('vendorpayments_invoicegrid_view_canceled');
            $resultPage->getLayout()->getUpdate()->removeHandle('vendorpayments_invoicegrid_view');
        }

        $this->registryObject->register('vendorpayments_invoices_view', $model);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
