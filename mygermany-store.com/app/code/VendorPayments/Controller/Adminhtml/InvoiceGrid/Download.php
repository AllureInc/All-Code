<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Download extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->invoiceModel = $invoiceModel;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if(isset($data['id'])){
            $model = $this->invoiceModel->load($data['id']);
            $itemIds = explode(',', $model->getSaleslistItemIds());
            $this->helper->downloadPdfAction($itemIds, $model->getSellerId());
            // echo "<pre>";
            // print_r($model->getData());
            // die;
        }
    }
}
