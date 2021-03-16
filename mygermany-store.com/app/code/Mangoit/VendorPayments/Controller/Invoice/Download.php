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

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Download extends Action
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
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        $this->helper = $helper;
        $this->invoiceModel = $invoiceModel;
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
        $data = $this->getRequest()->getParams();
        if(isset($data['id'])){
            $model = $this->invoiceModel->load($data['id']);
            if($model->getInvoiceTyp() == 1) {
                $this->helper->downloadReturnInvoicePdf($model, $model->getSellerId());
            } else {
                $itemIds = explode(',', $model->getSaleslistItemIds());
                $this->helper->downloadPdfAction($itemIds, $model->getSellerId());
            }
        }
    }
}
