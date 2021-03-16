<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Helper;


class OrderStatusNotification extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customer;
    protected $_scopeConfig;
    protected $attachmentContainer;
    protected $_transportBuilder;
    protected $_helper;
    protected $logger;
    protected $_mediaDirectory;
    protected $reader;

    const XML_PATH_EMAIL_RECIPIENT_SELLER = 'marketplace/email/order_processed_notification';
    const XML_PATH_EMAIL_RECIPIENT_ADMIN = 'marketplace/email/order_processed_notification_to_admin';
    
    public function __construct (
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\App\Helper\Context $context
    ) {   
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_customer = $customerRepositoryInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
        $this->_helper = $helper;
        $this->logger = $logger;
        $this->reader = $reader;
        parent::__construct($context);
    }

    public function getAdminDetails()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $data['email'] = $this->_scopeConfig->getValue('trans_email/ident_general/email', $storeScope);
        $data['name'] = $this->_scopeConfig->getValue('trans_email/ident_general/name', $storeScope);
        return $data;
    }

    public function getEmailTemplate($path, $store_id)
    {
        $emailTemplate = $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
        return $emailTemplate;
    }

    public function sendNotifications($orderid, $seller_id, $delivery_type)
    {
        $admin_details = $this->getAdminDetails();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($seller_id);
        $customer_details = ['name'=> $customer->getFirstname(), 'email'=> $customer->getData('email')];
        $store_id = $customer->getData('store_id');

        $sellerPostObject = new \Magento\Framework\DataObject();
        $adminPostObject = new \Magento\Framework\DataObject();        

        $forSellerNotification['name'] = $customer->getFirstname();
        $forSellerNotification['order_id'] = $orderid;
        $sellerPostObject->setData($forSellerNotification);

        $forAdminNotification['admin_name'] = $admin_details['name'];
        $forAdminNotification['order_id'] = $orderid;
        $forAdminNotification['name'] = $customer->getFirstname();
        $adminPostObject->setData($forSellerNotification);
       
        try {   
            
            $this->sendNotificationEmail('marketplace/email/order_processed_notification', $store_id, $sellerPostObject, $admin_details, $customer_details);      

            $this->sendNotificationEmail('marketplace/email/order_processed_notification_to_admin', $store_id, $adminPostObject, $customer_details, $admin_details);
            return ['status'=> true];
        } catch (Exception $e) {
            return ['status'=> false, 'message'=> ' '.$e->getMessage()];
        }


    }

    public function sendNotificationEmail($path, $store_id, $postObject, $sender, $receiver)
    {
        try {
            $this->logger->info('path: '.$path);
            $emailTemplate = $this->getEmailTemplate($path, $store_id);

            $this->logger->info('emailTemplate: '.$emailTemplate);

            $transport = $this->_transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store_id
                ]
            )->setTemplateVars(
                ['data' => $postObject]
            )->setFrom($sender)
            ->addTo($receiver['email']);

            $transport->getTransport()->sendMessage();
            
            $this->logger->info('## order_processed_notification has been sent to '.$receiver['email']);
            
        } catch (Exception $e) {
            $this->logger->info('## order_processed_notification has error:  '.$e->getMessage());
        }
    }

}