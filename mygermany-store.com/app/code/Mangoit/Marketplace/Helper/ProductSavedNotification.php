<?php
namespace Mangoit\Marketplace\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\MailException;
/**
 * 
 */
class ProductSavedNotification extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRODUCT_SAVE_NOTIFICATION = 'marketplace/email/product_save_notification';

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
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

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
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_objectManager = $objectManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_logger = $logger;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        parent::__construct($context);
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
        
        $this->_logger->info('### ProductSavedNotification ####'); 
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
    public function generateTemplate($emailTemplateVariables, $receiverInfo)
    {
        $senderInfo['name'] = $this->getConfigValue('trans_email/ident_general/name', 0);
        $senderInfo['email'] = $this->getConfigValue('trans_email/ident_general/email', 0);

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

    /**
     * [ORDER_RECEIVED_NOTIFICATION_TO_SELLER description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendProductSavedNotification($emailTemplateVariables, $receiverInfo, $sellerStoreId = 0)
    {
        $this->_template = $this->getTemplateId(self::PRODUCT_SAVE_NOTIFICATION, $sellerStoreId);
        $this->_inlineTranslation->suspend();

        /*----*/
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($emailTemplateVariables);
        /*----*/

        $this->generateTemplate(['data'=> $postObject], $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    public function getImageUrl($product)
    {
        return $imageUrl = $this->imageHelperFactory->create()->init($product, 'product_thumbnail_image')->getUrl();
    }
}