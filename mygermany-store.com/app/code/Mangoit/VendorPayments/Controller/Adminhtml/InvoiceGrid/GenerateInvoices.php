<?php
namespace Mangoit\VendorPayments\Controller\Adminhtml\InvoiceGrid;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class GenerateInvoices extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $_webkulHelper;
    protected $_monthlyInvoiceGeneration;
    protected $_weeklyInvoiceGeneration;
    protected $_messageManager;
    protected $_url;

    public function __construct(
        Context $context,
        \Webkul\Marketplace\Helper\Data $webkulHelper,
        \Mangoit\Marketplace\Cron\MonthlyInvoiceGeneration $monthlyInvoiceGeneration,
        \Mangoit\Marketplace\Cron\WeeklyInvoiceGeneration $weeklyInvoiceGeneration,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_webkulHelper = $webkulHelper;
        $this->_monthlyInvoiceGeneration = $monthlyInvoiceGeneration;
        $this->_weeklyInvoiceGeneration = $weeklyInvoiceGeneration;
        $this->_messageManager = $messageManager;
        $this->_url = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {        
        $data = $this->getRequest()->getParams();
        $sellerCollection = $this->_webkulHelper->getSellerCollection();
        $seller_details_array = [];

        foreach ($sellerCollection as $seller) {
            if ($seller->getIsSeller() != 0) {
                $seller_details_array[$seller->getGenerateInvoice()][] = $seller->getSellerId(); 
            }
        }

        if (isset($seller_details_array['monthly']) || isset($seller_details_array['weekly'])) {

            $result_array = $this->_weeklyInvoiceGeneration->executeCronJob($seller_details_array);
            $monthly_result_array = $this->_monthlyInvoiceGeneration->executeCronJob($seller_details_array);

            $url = $this->_url->getUrl('vendorpayments/invoicegrid/index', array(
                'sort' => 'entity_id',
                'dir' => 'desc'
            ));

            if (isset($result_array['error']) && isset($monthly_result_array['error'])) {
                $this->_messageManager->addError(__("Something went wrong while generating the weekly & monthly invoices. Please check the logs."));
                echo 0;
                exit();
            } elseif (!isset($result_array['error']) && isset($monthly_result_array['error'])) {
                $this->_messageManager->addError(__("Something went wrong while generating the weekly invoices. Please check the logs."));
                echo 1;
                exit();
            } elseif (isset($result_array['error']) && !isset($monthly_result_array['error'])) {
                $this->_messageManager->addError(__("Something went wrong while generating the monthly invoices. Please check the logs."));
                echo 2;
                exit();
            } else {
                $this->_messageManager->addSuccess(__("All the eligible order's invoices has been generated. The email notification has been sent to the vendor and admin too. Please <a target='_blank' href=".$url.">click here</a> to see generated invoices."));
                echo 3;
                exit();
            }
        }
    }
}
