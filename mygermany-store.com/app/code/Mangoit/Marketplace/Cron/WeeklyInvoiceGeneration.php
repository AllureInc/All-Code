<?php
namespace Mangoit\Marketplace\Cron;

class WeeklyInvoiceGeneration
{
    protected $_dateTime;
    protected $_logger;
    protected $_sellerInfo;
    protected $_isManual;
    protected $_url;
    protected $_session;
    protected $marketplaceHelper;
    protected $attachmentContainer;
    protected $_transportBuilder;
    protected $helper;
    protected $_sellerModel;
    protected $_customer;
    protected $_generateinvoice;


    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        \Magento\Customer\Model\Customer $customer,
        \Mangoit\VendorPayments\Controller\Index\Generateinvoice $generateinvoice,
        \Mangoit\VendorPayments\Helper\Data $helper
    )
    {

        $this->_dateTime = $dateTime;
        $this->_logger = $this->getCurrentLogger();
        $this->_session = $session;
        $this->_url = $url;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
        $this->_sellerModel = $sellerModel;
        $this->_customer = $customer;
        $this->_generateinvoice = $generateinvoice;
        $this->helper = $helper;
    }

    public function getWeeklyInvoiceGeneratorVendors()
    {
        $this->_logger->info("=== ### getWeeklyInvoiceGeneratorVendors Cron is running ### ===");
        $sellerCollection = $this->marketplaceHelper->getSellerCollection();
        $seller_details_array = [];

        foreach ($sellerCollection as $seller) {
            if ($seller->getIsSeller() != 0) {
                $seller_details_array[$seller->getGenerateInvoice()][] = $seller->getSellerId(); 
            }
        } 

        return $seller_details_array;
    }

    /*
    * This is the saparate logger file for the weekly invoice generation.
    *
    */
    public function getCurrentLogger()
    {
        date_default_timezone_set("Europe/Berlin");
        $date =  date("d-m-Y h:i:sa");
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/weekly_cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("=== WeeklyInvoiceGeneration === ");
        $logger->info("  Date & Time: ".$date);
        return $logger;
    }


    /*
    * This function is containing the main code for perform the action.
    *
    */
    public function execute()
    {
        $result_array = [];

        if (!isset($this->_sellerInfo['weekly'])) {
            $this->_logger->info("=== ### WeeklyInvoiceGeneratorVendors Cron is running ### ===");
            $this->_sellerInfo = $this->getWeeklyInvoiceGeneratorVendors();
        }

        /* Primany condition check */
        if (empty($this->_sellerInfo['weekly'])) {
            $this->_logger->info("=== No Seller Found ===");
            return $result_array;
            
        } else {
            /*---Loggers Start---*/
            $this->_logger->info("=== WeeklyInvoiceGeneration === execute function ===");
            $this->_logger->info('Seller Info: '.json_encode(array_unique($this->_sellerInfo['weekly'])));
            /*---Loggers End  ---*/

            try {
                $result_array = $this->generateInvoices();
                if ($this->_isManual == 1) {
                    return $result_array;
                } else {
                    $this->_logger->info("## Weekly Cron success ##");
                }
            } catch (Exception $e) {
                $this->_logger->info(" ## Error 1: ".$e->getMessage());
                if ($this->_isManual == 1) {
                    return $result_array;
                } else {
                    $this->_logger->info("## Weekly Cron faied ".$e->getMessage());
                }
            }
            
            /* When the action will called by admin area */
            if ($this->_isManual == 1) {
                return $result_array;
            }
        }
        

    }
    
    /*
    * This function is resposible for performing the cron action manually.
    * It will call the execute function.
    */
    public function executeCronJob($sellerInfo, $is_manual = 1)
    {
        $this->_sellerInfo = $sellerInfo;
        $this->_isManual = $is_manual;
        $result_array = $this->execute();
        return $result_array;
    }

    /*
    * This function will generate the invoices.
    *
    */
    public function generateInvoices()
    {
        $result_array = [];

        foreach (array_unique($this->_sellerInfo['weekly']) as $key => $sellerid) {

            /* 
            * This line will create the Invoice-PDF file.
            */
            $attachmentContent = $this->helper->getOrderPdfHtmlContent($sellerid, $eligible_orders = [], $forCron = 1);

            if ($attachmentContent != false) {
                $this->_logger->info('');
                $this->_logger->info("## Seller ID ".$sellerid." attachmentContent : ".json_encode($attachmentContent));
                $attachmentName = $this->helper->getPDFName($attachmentContent['invoiced_order_item_ids']);

                if(isset($attachmentContent['str'])) {
                    $this->helper->attachPdf(
                        $attachmentContent['str'],
                        $attachmentName,
                        $this->attachmentContainer
                    );
                }

                /*
                * Here we prepare the data for the email.
                */
                $invoice_data = [
                    'seller_id'=> $sellerid,
                    'seller_data'=> $this->_sellerModel->load($sellerid, 'seller_id'),
                    'seller_mage_obj'=> $this->_customer->load($sellerid),
                    'attachment_container'=> $this->attachmentContainer,
                    'logger' => $this->_logger
                ];

                try {

                    /*
                    * Remember: 
                    * First you need to send the email to admin and then
                    * send email to the vendor. Because after sending the
                    * attachement email to the vendor the attachment will
                    * reset.
                    */
                    try {
                        $email_result_admin = $this->_generateinvoice->sendInvoiceTo_myGermany($invoice_data);
                        $email_result = $this->_generateinvoice->sendInvoiceTo_Vendor($invoice_data);
                        
                    } catch (Exception $e) {
                        $this->_logger->info("## Weekly Invoice Exception: ".$e->getMessage());
                    }

                    if ($email_result['status'] == true && $email_result_admin['status'] == true) {
                        $result_array['success'][] = ['seller_id'=> $sellerid, 'message'=> __("Email has been sent successfully.")];
                        $this->_logger->info("## Email has been sent successfully. Seller ID: ".$sellerid);
                    } else {
                        $result_array['error'][] = ['seller_id'=> $sellerid, 'message'=> $email_result['message']];
                        $this->_logger->info("## Email has error. Seller ID: ".$sellerid." Message: ".$email_result['message']);
                    }
                } catch (Exception $e) {
                    $result_array['error'][] = ['seller_id'=> $sellerid, 'message'=> $e->getMessage()];
                }

            } else {
                $this->_logger->info("## Weekly Invoice is not available for this Seller ID : ".$sellerid);
            }

        } 
        return $result_array;
    }
}
