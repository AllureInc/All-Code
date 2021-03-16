<?php
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryObject;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Registry $registryObject
    ) {
        $this->invoiceModel = $invoiceModel;
        $this->registryObject = $registryObject;
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
        // echo "<pre>";
        // print_r($model->getData());
        // die;

        $this->registryObject->register('vendorpayments_invoices_view', $model);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
