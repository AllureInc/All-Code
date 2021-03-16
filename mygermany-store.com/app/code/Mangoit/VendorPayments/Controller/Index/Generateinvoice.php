<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Generateinvoice extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_url;
    protected $_session;
    protected $marketplaceHelper;
    protected $attachmentContainer;
    protected $_transportBuilder;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    protected $logger;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Mangoit\VendorPayments\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->logger = $this->getCurrentLogger();
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    /*public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }*/

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketplaceHelper->isSeller();
        $sellerId = $this->marketplaceHelper->getCustomerId();

        if(($this->getRequest()->getParam('makeDownload') == 1) && $this->_session->getSaleslistItemIds()){
            $getSaleslistItemIds = $this->_session->getSaleslistItemIds();
            $this->_session->unsSaleslistItemIds();
            return $this->helper->downloadPdfAction($getSaleslistItemIds, $sellerId);
        }

        if ($isPartner == 1) {

            $attachmentContent = $this->helper->getOrderPdfHtmlContent();

            /* This code will be use when admin wants to send the invoice after 1-2 days */
            // if(isset($attachmentContent['invoiced_order_item_ids']) && empty($attachmentContent['invoiced_order_item_ids'])) {
            //     $daysAllwInvcIn = $this->helper->getConfigValue(
            //                 'marketplace/general_settings/allow_invoice_in',
            //                 $this->helper->getStore()->getStoreId()
            //             );
            //     $this->messageManager->addError(__('To generate invoice, your order should be in received status and it should pass %1 day(s).', $daysAllwInvcIn));
            //     return $this->resultRedirectFactory->create()->setPath(
            //         '*/invoice',
            //         ['_secure' => $this->getRequest()->isSecure()]
            //     );
            // }
            /* Code Ends */

            $this->logger->info(" Attachement Data: ");
            if (isset($attachmentContent['invoiced_order_item_ids']) && empty($attachmentContent['invoiced_order_item_ids'])) {
                $this->logger->info(print_r($attachmentContent['invoiced_order_item_ids'], true));
            } else {
                
            }


            $attachmentName = $this->helper->getPDFName($attachmentContent['invoiced_order_item_ids']);
            if(isset($attachmentContent['str'])) {
                $this->helper->attachPdf(
                    $attachmentContent['str'],
                    $attachmentName,
                    $this->attachmentContainer
                );
            }

            $this->sendInvoiceTo_myGermany();
            $this->sendInvoiceTo_Vendor();
            $this->messageManager->addSuccess(__('Invoice has been generated and sent to myGermany.'));
            $this->_session->setSaleslistItemIds($attachmentContent['invoiced_order_item_ids']);

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure(), 'makeDownload' => 1]
            );
           
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }


    public function sendInvoiceTo_myGermany($invoice_data = [])
    {
        $seller_store_id = 7;
        $this->logger->info(" ## Sending Invoice to myGermany Admin ##");
        try {
            //Email Address of Store Owner.
            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );

            /*$generalEmail = 'verma.praveen192@gmail.com';*/
            
            $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );

            $this->logger->info(" ## Admin Email ID: ".$generalEmail);

            /*$sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();*/

            if (!empty($invoice_data)) {
                $sellerData = $invoice_data['seller_data'];
                $sellerMageObj = $invoice_data['seller_mage_obj'];
                $logger = $invoice_data['logger'];
                $this->attachmentContainer = $invoice_data['attachment_container'];
                $this->logger->info("## admin email sellerMageObj: ".json_encode($sellerMageObj->getData('store_id')));                
            } else {
                $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
                $sellerMageObj = $this->marketplaceHelper->getCustomer();

            }

            $sellerName = $sellerMageObj->getName();

            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['sellername'] = $sellerName;
            $postObjectData['sellerid'] = $sellerData->getSellerId();
            $postObjectData['url'] = $this->marketplaceHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$sellerData->getShopUrl());
            $postObjectData['phone'] = $sellerData->getContactNumber();

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($postObjectData);

            $sender = [
               'name' => $sellerName,
               'email' => $sellerMageObj->getEmail()
            ];

            $this->logger->info(" ## Email is send by ".$sellerName." with email ID: ".$sellerMageObj->getEmail());

            if ($sellerMageObj->getData('store_id') == 0) {
                $seller_store_id = 7;
            } else {
                $seller_store_id = $sellerMageObj->getData('store_id');
            }

            //Value of custom email template from store.
            $emailTemplate = $this->helper->getTemplateId('marketplace/general_settings/vendor_invoice_email_template', $seller_store_id);

            $this->logger->info('### Admin Seller Store ID: '.$seller_store_id.' ###');

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $seller_store_id
                        
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($generalEmail);

            if ($this->attachmentContainer->hasAttachments()) {
                foreach ($this->attachmentContainer->getAttachments() as $attachment) {
                     $transport->addAttachment($attachment->getContent(),$attachment->getFileName(), $attachment->getMimeType());
                }
            }

            $transport->getTransport()->sendMessage();

            if (isset($invoice_data['seller_id'])) {
                return ['status'=> true, 'message'=> __('Email has been sent successfully.')];
            }
        } catch (\Exception $e) {

            $this->logger->info('### Exception: '.$e->getMessage());

            if (isset($invoice_data['seller_id'])) {
                return ['status'=> false, 'message'=> $e->getMessage()];
            }
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function sendInvoiceTo_Vendor($invoice_data = [])
    {
        $seller_store_id = 7;

        try {
            //Email Address of Store Owner.
            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );
            $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );

            $this->logger->info('### Sending Invoice to Vendor ##');

            if (!empty($invoice_data)) {
                $sellerData = $invoice_data['seller_data'];
                $sellerMageObj = $invoice_data['seller_mage_obj'];
                $logger = $invoice_data['logger'];
                $this->attachmentContainer = $invoice_data['attachment_container'];

                $this->logger->info("## vendor email sellerMageObj: ".json_encode($sellerMageObj->getData('store_id')));  

            } else {
                $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
                $sellerMageObj = $this->marketplaceHelper->getCustomer();
            }

            $sellerName = $sellerMageObj->getName();
            $this->logger->info('### Vendor Name: '.$sellerName);
            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['sellername'] = $sellerName;
            $postObjectData['sellerid'] = $sellerData->getSellerId();
            $postObjectData['url'] = $this->marketplaceHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$sellerData->getShopUrl());
            $postObjectData['phone'] = $sellerData->getContactNumber();

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($postObjectData);

            $sender = [
               'name' => $generalName,
               'email' => $generalEmail,
            ];

            if ($sellerMageObj->getData('store_id') == 0) {
                $seller_store_id = 7;
            } else {
                $seller_store_id = $sellerMageObj->getData('store_id');
            }

            //Value of custom email template from store.
            $emailTemplate = $this->helper->getTemplateId('marketplace/general_settings/vendor_invoice_email_template_vendor_copy', $seller_store_id);

            $this->logger->info('### Vendor Seller Store ID: '.$seller_store_id.' ###');

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $seller_store_id
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($sellerMageObj->getEmail());

            if ($this->attachmentContainer->hasAttachments()) {
                foreach ($this->attachmentContainer->getAttachments() as $attachment) {
                    $transport->addAttachment($attachment->getContent(),$attachment->getFileName(), $attachment->getMimeType());
                    /*$transport->addAttachment1($attachment);*/
                }
                $this->attachmentContainer->resetAttachments();
            }

            $transport->getTransport()->sendMessage();

            if (isset($invoice_data['seller_id'])) {
                return ['status'=> true, 'message'=> __('Email has been sent successfully.')];
            }

        } catch (\Exception $e) {

            $this->logger->info('### Vendor Exception: '.$e->getMessage());

            if (isset($invoice_data['seller_id'])) {
                return ['status'=> false, 'message'=> $e->getMessage()];
            }

            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function getCurrentLogger()
    {
        date_default_timezone_set("Europe/Berlin");
        $date =  date("d-m-Y h:i:sa");
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Generateinvoice_Email_Logger.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("=== Generateinvoice_Email_Logger === ");
        $logger->info("  Date & Time: ".$date);
        return $logger;
    }
}
