<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Invoice;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Model\Sellertransaction;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_url;
    protected $_session;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryObject;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;

    /**
     * @var Sellertransaction
     */
    protected $sellertransaction;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Registry $registryObject,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        NotificationHelper $notificationHelper,
        Sellertransaction $sellertransaction
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        $this->helper = $helper;
        $this->invoiceModel = $invoiceModel;
        $this->registryObject = $registryObject;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->notificationHelper = $notificationHelper;
        $this->sellertransaction = $sellertransaction;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
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

            if($model->getSellerId() != $this->marketplaceHelper->getCustomerId()) {
                $this->messageManager->addError(__('You can not view this invoice.'));
                $this->_redirect('*/*/');
                return;
            }

            $transaction = $this->sellertransaction->load($model->getTransactionId(), 'transaction_id');
            // print_r($transaction->getData());
            // die;
            $type = Notification::TYPE_TRANSACTION;
            $this->notificationHelper->updateNotification(
                $transaction,
                $type
            );
        }
        $resultPage = $this->_resultPageFactory->create();
        if($model->getInvoiceTyp() == 1){
            $resultPage->addHandle('vendorpayments_invoice_view_canceled');
            $resultPage->getLayout()->getUpdate()->removeHandle('vendorpayments_invoice_view');
        }

        $this->registryObject->register('vendorpayments_invoices_view_vendor', $model);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
