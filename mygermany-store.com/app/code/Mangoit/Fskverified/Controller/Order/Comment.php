<?php
namespace Mangoit\Fskverified\Controller\Order;
/**
* 
*/
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;

class Comment extends Action
{
    protected $transport;
	protected $_objectManager;
    protected $scopeConfig; // for email
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $resultRedirectFactory;
    protected $_inlineTranslation; // for Email
    protected $transportBuilder; //for email

	public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect
		)
	{
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_objectManager = $objectmanager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->resultRedirectFactory = $resultRedirect;
        $this->scopeConfig = $scopeConfig; // for email
        $this->transportBuilder = $transportBuilder; // for email
		parent::__construct($context);
	}
    
    public function execute()
    {
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $commentCollection = $this->_objectManager->create('Mangoit\Fskverified\Model\Comment');
        $currentDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $currentDate =  $currentDate->gmtDate("M d, Y   H:i:s A");
        $customerId = $customerSession->getCustomer()->getId();
        $customerName = $customerSession->getCustomer()->getName();

        $resultRedirect = $this->resultRedirectFactory->create();
        $parameters = $this->getRequest()->getParams();
        $orderId = $parameters['orderId'];
        $commentData = $parameters['commentName'];

        $commentCollection->setOrderId($orderId);
        $commentCollection->setSellerId($customerId);
        $commentCollection->setSellerName($customerName);
        $commentCollection->setComment($commentData);
        $commentCollection->setCreatedOn($currentDate);
        try {
            $commentCollection->save();
            if ($this->sendEmailToAdmin($customerId, $orderId, $commentData)) {
               $this->messageManager->addSuccess(__('Comment Saved.'));
            } else {
                $this->messageManager->addError(__('Comment not saved.'));
            }

        } catch (Exception $e) {
            $this->messageManager->addError(__('Comment not saved'));
        }

        $resultRedirect->setRefererUrl();
		return $resultRedirect;
    }
 
    public function getScopeConfigValue($configPath) // for email
    {
        $scopeValue = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $scopeValue;
    }

    public function sendEmailToAdmin($customerId, $orderId, $commentData) // for email
    {
            
            $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
            $sellerCollection = $this->_objectManager->get('Webkul\Marketplace\Model\Seller');

            $customerId = $customerSession->getCustomer()->getId();
            $customerName = $customerSession->getCustomer()->getName();
            $customerEmail = $customerSession->getCustomer()->getEmail();
            $filteredSeller = $sellerCollection->getCollection()->addFieldToFilter('seller_id',['eq' => $customerId]);
            if (empty($filteredSeller)) {
                return false;
            } else {
                $shopName = $sellerCollection->load($customerId, 'seller_id')->getShopTitle();
                if (empty($shopName)) {
                    $shopName = 'shop-name-not-available';
                }

            }
            

            /* Add sender and receiver details here */
            $toName  = $customerName; // sender
            $toEmail = $customerEmail; // sender

            $salesName = $this->getScopeConfigValue('trans_email/ident_general/name'); // receiver
            $salesEmail = $this->getScopeConfigValue('trans_email/ident_general/email');  // receiver

            $urlBuilder = $this->_objectManager->create('Magento\Framework\UrlInterface');
            $adminUrl = $urlBuilder->getBaseUrl()."admin";
            $postObject = new \Magento\Framework\DataObject();

            $postObject->setData(['name'=> $shopName, 'orderId'=> $orderId, 'commentData'=> $commentData, 'adminUrl'=> $adminUrl]); 
            $sender = [
                'name' => $toName,
                'email' => $toEmail,
            ];

            $this->_inlineTranslation->suspend();
            $transport = $this->transportBuilder
            ->setTemplateIdentifier('marketplace_email_mis_order_comment')
            ->setTemplateOptions(
              [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
              ]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($sender)
            ->addTo($salesEmail)->getTransport(); // ->addTo($toEmail);                
            try {
                $transport->sendMessage();
            } catch (Exception $e) {
                return false;
            }

            $this->_inlineTranslation->resume();
            return true;
            /** Email Code End **/
    }

}