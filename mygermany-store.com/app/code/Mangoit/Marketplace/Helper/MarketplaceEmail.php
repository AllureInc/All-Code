<?php
namespace Mangoit\Marketplace\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\MailException;
/**
 * 
 */
class MarketplaceEmail extends \Webkul\Marketplace\Helper\Email
{
    const COMPLIANCE_CHECK_NOTIFICATION_TO_CUSTOMER = 'marketplace/email/compliance_check_customer_notification';    
    const COMPLIANCE_CHECK_NOTIFICATION_TO_ADMIN = 'marketplace/email/compliance_check_admin_notification';

    const ORDER_PROCESS_NOTIFICATION_TO_SELLER = 'marketplace/email/order_process_notification_to_seller';
    const ORDER_RECEIVED_NOTIFICATION_TO_SELLER = 'marketplace/email/order_received';
    const FSK_EMAIL_REQUEST = 'marketplace/email/fsk_email';
    const FSK_EMAIL_APPROVAL = 'marketplace/email/fsk_approval';
    const RETURN_PAID_ORDER_VENDOR = 'marketplace/email/return_paid_order_vendor';
    const RETURN_ORDER_VENDOR = 'marketplace/email/return_order_vendor';
    const AFTER_PAY_EMAIL_TO_ADMIN = 'marketplace/email/afterpay_email_to_admin';
    const PREORDER_IN_STOCK_NOTIFY = 'marketplace/email/preorder_in_stock_notify';

    const XML_PATH_EMAIL_SELLER_APPROVAL = 'marketplace/email/seller_approve_notification_template';
    const XML_PATH_EMAIL_BECOME_SELLER = 'marketplace/email/becomeseller_request_notification_template';
    const XML_PATH_EMAIL_SELLER_DISAPPROVE = 'marketplace/email/seller_disapprove_notification_template';
    const XML_PATH_EMAIL_SELLER_DENY = 'marketplace/email/seller_deny_notification_template';
    const XML_PATH_EMAIL_PRODUCT_DENY = 'marketplace/email/product_deny_notification_template';
    const XML_PATH_EMAIL_NEW_PRODUCT = 'marketplace/email/new_product_notification_template';
    const XML_PATH_EMAIL_EDIT_PRODUCT = 'marketplace/email/edit_product_notification_template';
    const XML_PATH_EMAIL_DENY_PRODUCT = 'marketplace/email/product_deny_notification_template';
    const XML_PATH_EMAIL_PRODUCT_QUERY = 'marketplace/email/askproductquery_seller_template';
    const XML_PATH_EMAIL_SELLER_QUERY = 'marketplace/email/askquery_seller_template';
    const XML_PATH_EMAIL_ADMIN_QUERY = 'marketplace/email/askquery_admin_template';
    const XML_PATH_EMAIL_APPROVE_PRODUCT = 'marketplace/email/product_approve_notification_template';
    const XML_PATH_EMAIL_DISAPPROVE_PRODUCT = 'marketplace/email/product_disapprove_notification_template';
    const XML_PATH_EMAIL_ORDER_PLACED = 'marketplace/email/order_placed_notification_template';
    const XML_PATH_EMAIL_ORDER_INVOICED = 'marketplace/email/order_invoiced_notification_template';
    const XML_PATH_EMAIL_SELLER_TRANSACTION = 'marketplace/email/seller_transaction_notification_template';
    const XML_PATH_EMAIL_LOW_STOCK = 'marketplace/email/low_stock_template';
    const XML_PATH_EMAIL_WITHDRAWAL = 'marketplace/email/withdrawal_request_template';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_messageManager;

    protected $_logger;

    /**
     * @param Magento\Framework\App\Helper\Context              $context
     * @param Magento\Framework\ObjectManagerInterface          $objectManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param Session                                           $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_objectManager = $objectManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
         $this->_logger = $logger;
        /*parent::__construct($context, $objectManager, $inlineTranslation, $transportBuilder, $messageManager, $storeManager, $logger, $customerSession);*/
        parent::__construct($context, $inlineTranslation, $transportBuilder, $messageManager, $storeManager, $customerSession, $objectManager, $logger);
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath,$sellerStoreId=0)
    {
		if($sellerStoreId)
			$storeId = $sellerStoreId;
		else
			$storeId = $this->getStore()->getStoreId();
		
		$this->_logger->info('### Marketplace Email Logger ####'); 
        $this->_logger->info("## Store ID for Email: ". $storeId); 
        $this->_logger->info("## current template ID: ". $this->getConfigValue($xmlPath, $storeId)); 
        $this->_logger->info('###  ####'); 
        return $this->getConfigValue($xmlPath, $storeId);
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], isset($receiverInfo['name']) ? $receiverInfo['name'] : '');

          $this->_logger->info('## template : '.$this->_template);
          
          $this->_logger->info("## receiverInfo: email: ". $receiverInfo['email']);
          $this->_logger->info("## sender: email: ". $senderInfo['email']);

        return $this;
    }

    /*transaction email template*/
    /**
     * [sendQuerypartnerEmail description].
     *
     * @param Mixed $data
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendQuerypartnerEmail($data, $emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        if (isset($data['product-id'])) {
            $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_QUERY,$sellerStoreId);
        } else {
            $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_QUERY,$sellerStoreId);
        }
        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /**
     * [sendPlacedOrderEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendPlacedOrderEmail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_logger->info(" = ");
        $this->_logger->info("#### sendPlacedOrderEmail ####");
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_PLACED,$sellerStoreId);

        $this->_logger->info(" = ");
        $this->_logger->info("#### $this->_template: ".$this->_template." ####");
        
        $this->_logger->info(" = ");
        $this->_logger->info("#### $sellerStoreId: ".$sellerStoreId." ####");

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendInvoicedOrderEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendInvoicedOrderEmail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_INVOICED,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendLowStockNotificationMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendLowStockNotificationMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_LOW_STOCK,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendSellerPaymentEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendSellerPaymentEmail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0, $attachment_data = 0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_TRANSACTION,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            if ($attachment_data->hasAttachments()) {
                foreach ($attachment_data->getAttachments() as $attachment) {
                    $this->_transportBuilder->addAttachment($attachment->getContent(),$attachment->getFileName(), $attachment->getMimeType());
                }
            }
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendProductStatusMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendProductStatusMail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_APPROVE_PRODUCT,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendProductUnapproveMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendProductUnapproveMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_DISAPPROVE_PRODUCT, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [FSK_EMAIL_REQUEST description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendApprovalFskEmail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_logger->info('### Marketplace Email Logger: FSK_EMAIL_APPROVAL ####'); 
        $this->_logger->info('### FSK_EMAIL_REQUEST store:'.$sellerStoreId.' ####'); 

        $this->_template = $this->getTemplateId(self::FSK_EMAIL_APPROVAL, $sellerStoreId);
        /* Logger start*/
        $this->_logger->info('### Marketplace Email Logger ####'); 
        $this->_logger->info("## sendNewFskRequestEmail StoreId: ". $sellerStoreId); 
        $this->_logger->info("## Receiver's Email: ". $receiverInfo['email']);  
        $this->_logger->info('#############'); 
        /* Logger ends */
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [RETURN_PAID_ORDER_VENDOR description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendReturnPaidOrderVendorEmail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_logger->info('### Marketplace Email Logger: RETURN_PAID_ORDER_VENDOR ####'); 
        $this->_logger->info('### RETURN_PAID_ORDER_VENDOR store:'.$sellerStoreId.' ####'); 

        $this->_template = $this->getTemplateId(self::RETURN_PAID_ORDER_VENDOR, $sellerStoreId);
        /* Logger start*/
        $this->_logger->info('### Marketplace Email Logger ####'); 
        $this->_logger->info("## sendReturnPaidOrderVendorEmail StoreId: ". $sellerStoreId); 
        $this->_logger->info("## Receiver's Email: ". $receiverInfo['email']);  
        $this->_logger->info('#############'); 
        /* Logger ends */
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [RETURN_ORDER_VENDOR description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendReturnOrderVendor($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_logger->info('### Marketplace Email Logger: RETURN_ORDER_VENDOR ####'); 
        $this->_logger->info('### RETURN_ORDER_VENDOR store:'.$sellerStoreId.' ####'); 

        $this->_template = $this->getTemplateId(self::RETURN_ORDER_VENDOR, $sellerStoreId);
        /* Logger start*/
        $this->_logger->info('### Marketplace Email Logger ####'); 
        $this->_logger->info("## sendReturnOrderVendor StoreId: ". $sellerStoreId); 
        $this->_logger->info("## Receiver's Email: ". $receiverInfo['email']);  
        $this->_logger->info('#############'); 
        /* Logger ends */
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [FSK_EMAIL_REQUEST description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendNewFskRequestEmail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0, $filesContents, $file_name)
    {
        $this->_logger->info('### Marketplace Email Logger: FSK_EMAIL_REQUEST ####'); 
        $this->_logger->info('### FSK_EMAIL_REQUEST store:'.$sellerStoreId.' ####'); 

        $this->_template = $this->getTemplateId(self::FSK_EMAIL_REQUEST, $sellerStoreId);
        /* Logger start*/
        $this->_logger->info('### Marketplace Email Logger ####'); 
        $this->_logger->info("## sendNewFskRequestEmail StoreId: ". $sellerStoreId); 
        $this->_logger->info("## Receiver's Email: ". $receiverInfo['email']);  
        $this->_logger->info('#############'); 
        /* Logger ends */
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            echo "<pre>";
            print_r(get_class_methods($transport));
            die();
            $transport->addAttachment(
                $filesContents,
                \Zend_Mime::TYPE_OCTETSTREAM,
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $file_name
            );
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            return false;
        }
        $this->_inlineTranslation->resume();
        return true;
    }


    /**
     * [sendNewSellerRequest description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendNewSellerRequest($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_logger->info('### Marketplace Email Logger: sendNewSellerRequest ####'); 
        $this->_logger->info('### sendNewSellerRequest:'.$sellerStoreId.' ####'); 

        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_BECOME_SELLER,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendSellerApproveMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendSellerApproveMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_APPROVAL,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendSellerDisapproveMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendSellerDisapproveMail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_logger->info('### Marketplace Email Logger: sendSellerDisapproveMail ####'); 
        $this->_logger->info('### sendSellerDisapproveMail: sellerStoreId:'.$sellerStoreId.' ####');
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_DISAPPROVE, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendSellerDenyMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendSellerDenyMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_DENY,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendProductDenyMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendProductDenyMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_DENY,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendNewProductMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendNewProductMail($emailTemplateVariables, $senderInfo, $receiverInfo, $editFlag,$sellerStoreId=0)
    {
        if ($editFlag == null) {
            $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_PRODUCT,$sellerStoreId);
        } else {
            $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_EDIT_PRODUCT,$sellerStoreId);
        }

        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendQueryAdminEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function askQueryAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_QUERY,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendWithdrawalRequestMail].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendWithdrawalRequestMail($emailTemplateVariables, $senderInfo, $receiverInfo,$sellerStoreId=0)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_WITHDRAWAL,$sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    public function sendCustomEmail($email_template, $sellerStoreId = 0, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId($email_template, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        /*$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);*/
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    public function sendPreorderInStockEmail($sellerStoreId = 0, $emailTemplateVariables, $senderInfo, $receiverInfo, $templateOptions)
    {
        $this->_template = $this->getTemplateId(self::PREORDER_IN_STOCK_NOTIFY, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        /*$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);*/
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->setTemplateOptions($templateOptions)->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [RETURN_ORDER_VENDOR description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendNotificationToSellerForProcessOrder($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::ORDER_PROCESS_NOTIFICATION_TO_SELLER, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [ORDER_RECEIVED_NOTIFICATION_TO_SELLER description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendOrderReceivedNotificationToSeller($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::ORDER_RECEIVED_NOTIFICATION_TO_SELLER, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [COMPLIANCE_CHECK_NOTIFICATION_TO_CUSTOMER description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendComplianceCheckNotificationToCustomer($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::COMPLIANCE_CHECK_NOTIFICATION_TO_CUSTOMER, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [COMPLIANCE_CHECK_NOTIFICATION_TO_ADMIN description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendComplianceCheckNotificationToAdmin($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::COMPLIANCE_CHECK_NOTIFICATION_TO_ADMIN, $sellerStoreId);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(['data'=> $emailTemplateVariables], $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }
}