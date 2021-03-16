<?php
namespace Mangoit\Fskverified\Controller\Fsk;
/**
* 
*/
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;

class Fskdoc extends Action
{
    const FSK_EMAIL_REQUEST = 'marketplace/email/fsk_email';

    protected $transport;
	protected $_objectManager;
    protected $scopeConfig; // for email
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $resultRedirectFactory;
    protected $_inlineTranslation; // for Email
    protected $transportBuilder; //for email
    protected $reader;
    protected $ioAdapter;
    protected $_storeManager;

	public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\Filesystem\Io\File $ioAdapter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
		)
	{
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_objectManager = $objectmanager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->resultRedirectFactory = $resultRedirect;
        $this->scopeConfig = $scopeConfig; // for email
        $this->transportBuilder = $transportBuilder; // for email
        $this->reader = $reader;
        $this->ioAdapter = $ioAdapter;
        $this->_storeManager = $storeManager;
		parent::__construct($context);
	}
    
    public function execute()
    {
        $parameters = $this->getRequest()->getParams();
        
        $_urlInterface = $this->_objectManager->create('\Magento\Framework\UrlInterface');

        $_logger = $this->_objectManager->create('\Psr\Log\LoggerInterface');

        $resultRedirect = $this->resultRedirectFactory->create();

        $files = $this->getRequest()->getFiles('uploadFile');
        $media = $this->_mediaDirectory->getAbsolutePath('fskVerifiedUserDocs/');
        $uploaderFile = $this->_fileUploaderFactory->create(
                                ['fileId' => 'uploadFile']
                            );

        $uploaderFile->setAllowedExtensions(['jpg', 'jpeg', 'png', 'pdf']);
        $file_name = rand().$_FILES['uploadFile']['name'];
        $uploaderFile->setAllowRenameFiles(true);
        $file_type = $_FILES['uploadFile']['type'];
        $file_name = str_replace(' ', '_', $file_name);
        $uploaderFile->save($media, $file_name);
        try {
            /*$this->sendEmailToAdmin($file_name);*/
            if ($this->sendEmailToAdmin($file_name, $file_type)) {
                
                   $this->messageManager->addSuccess(__('Your document has been uploaded successfully. You will receive a confirmation email on your registered email id. Thank you.'));
                   try {
                        $this->addTocartProduct($parameters);   
                   } catch (Exception $e) {
                        // $this->messageManager->addError(__('We can\'t add the item to the cart right now.'));
                        $_logger->info($e->getMessage());
                   }               

            } else {
                    $this->messageManager->addError(__('File not uploaded.')); 
            }            
        } catch (Exception $e) {
            $_logger->info($e->getMessage());
        }
        $resultRedirect->setRefererUrl();
		return $resultRedirect;                            
    }
    
    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function addTocartProduct($parameters)
    {
        if ( (isset($parameters['post_wishlist'])) && ($parameters['post_wishlist'] != 0) ) {
            if ( ($parameters['post_product_id'] != null) && ($parameters['post_product_qty'] != null) ) {
                $helper = $this->_objectManager->create('Mangoit\Fskverified\Helper\Data');
                $result = $helper->addProductToCart($parameters['post_product_id'], $parameters['post_product_qty']);
            }
        }
    }
 
    public function getScopeConfigValue($configPath) // for email
    {
        $scopeValue = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $scopeValue;
    }

    public function sendEmailToAdmin($file_name, $file_type) // for email
    {
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $customerData = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($customerSession->getCustomer()->getId());
        
        $marketplaceEmailHelper = $this->_objectManager->get('Mangoit\Marketplace\Helper\MarketplaceEmail');
        $templateId = $marketplaceEmailHelper->getTemplateId(self::FSK_EMAIL_REQUEST, $customerData->getData('store_id'));
        
        $customerId = $customerSession->getCustomer()->getId();
        $customerName = $customerSession->getCustomer()->getName();
        $customerEmail = $customerSession->getCustomer()->getEmail();

        /* Add sender and receiver details here */
        $toName  = $customerName; // sender
        $toEmail = $customerEmail; // sender

        $salesName = $this->getScopeConfigValue('trans_email/ident_general/name'); // receiver
        $salesEmail = $this->getScopeConfigValue('trans_email/ident_general/email');  // receiver
           
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData(['name'=> $toName,]);

        $sender = [
            'name' => $toName,
            'email' => $toEmail,
        ];

        $pathToEmalFile1 =  $this->_mediaDirectory->getAbsolutePath('fskVerifiedUserDocs/'.$file_name);
        $this->_inlineTranslation->suspend();
        $transport = $this->transportBuilder
        // ->setTemplateIdentifier('marketplace_email_fsk_email')
        ->setTemplateIdentifier($templateId)
        ->setTemplateOptions(
        [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ]
        )
        ->setTemplateVars(['data' => $postObject])
        ->setFrom($sender)
        ->addTo($salesEmail); // ->addTo($toEmail);

        if (file_exists($pathToEmalFile1)) {
            chmod($pathToEmalFile1, 0777);
        } else {
            return false;
        }
        $filesContents = $this->reader->fileGetContents($pathToEmalFile1);

        $transport->addAttachment($filesContents, $file_name, $file_type);
        /*$transport->addAttachment(
            $filesContents,
            \Zend_Mime::TYPE_OCTETSTREAM,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $file_name
        );*/
        try {
            $transport->getTransport()->sendMessage();
                
        } catch (Exception $e) {
            print_r($e->getMessage());
        }

        $this->_inlineTranslation->resume();
        /** Email Code End **/
            
        return true;
    }
}