<?php
namespace Mangoit\SortMenu\Helper;

/**
 * Custom Module Email helper
 */
class EmailToAdmin extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'contact/email/order_notification_to_admin';
    const XML_PATH_RECEIVER  = 'contact/email/recipient_email';
    const XML_PATH_EMAIL_SENDER  = 'trans_email/ident_general/email';

    /* Here section and group refer to name of section and group where you create this field in configuration*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var string
    */
    protected $temp_id;
    
    /**
     * @var string
    */
    protected $_logger;

    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder; 
        $this->_logger = $logger;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
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
     * Return store 
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description]  with template file and tempaltes variables values                
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($emailTemplateVariables);
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
        ->setTemplateOptions(
            [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, /* here you can defile area and
                        store of template for which you prepare it */
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
        ->setTemplateVars(['data'=> $postObject])
        ->setFrom($senderInfo)
        ->addTo($receiverInfo['email'],$receiverInfo['name']);
        return $this;        
    }

    /**
     * [sendNotificationEmailToAdmin description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    /* your send mail method*/
    public function sendNotificationEmailToAdmin($emailTemplateVariables,$senderInfo,$receiverInfo)
    {

        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);
        $this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);    
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    public function sendOrderNotification($orderDetails)
    {
        $data['order_id'] = $orderDetails->getIncrementId();
        $payment = $orderDetails->getPayment();
        $method = $payment->getMethodInstance();
        $data['payment_method'] = $method->getTitle();
        $data['name'] = $orderDetails->getCustomerFirstname().' '.$orderDetails->getCustomerLastname();
        $data['shipping_method'] = $orderDetails->getShippingMethod();
        $data['total_qty_ordered'] = $orderDetails->getTotalQtyOrdered();

        $sender = ['name'=> 'myGermany Admin', 'email'=> $this->getConfigValue(self::XML_PATH_EMAIL_SENDER, 0)];
        $receiver = ['name'=> 'Admin', 'email'=> $this->getConfigValue(self::XML_PATH_RECEIVER, 0)];
        try {
            $this->sendNotificationEmailToAdmin($data,$sender,$receiver);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }

        return true;



    }

}