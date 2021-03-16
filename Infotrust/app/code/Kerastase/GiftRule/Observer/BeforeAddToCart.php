<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory; 

class BeforeAddToCart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_dataObjectFactory;

    /**
     * @var storeManager
     */
    protected $_storeManager; 

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var $messageManager
     */
    protected $_messageManager; 

    /**
     * @var $resultFactory
     */
    protected $_resultFactory;

    /**
     * @var $quote
     */
    protected $_quote; 

    /**
     * @var $redirect
     */
    protected $_redirect;

    /**
     * @var $checkoutSession
     */
    protected $_checkoutSession;

	const XML_PATH_FOR_RESTRICT_SETTING = "kerastase_giftrule/restric_add_to_cart/restrict";
	const XML_PATH_FOR_RESTRICT_MESSAGE = "kerastase_giftrule/restric_add_to_cart/restrict_message";

	public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_dataObjectFactory        = $dataObjectFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_messageManager = $messageManager;
        $this->_resultFactory = $resultFactory;
        $this->_quote = $quote;
        $this->_redirect = $redirect;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
    	$quote = $this->_quote->getQuote();
    	$restrictSetting = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_RESTRICT_SETTING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $restrictMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_RESTRICT_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $quoteItems = $quote->getItemsQty();
        if($quoteItems>=1) {
	    	foreach ($quote->getAllItems() as $item) {
	    		if($item->getPrice() == 0 && $restrictSetting) {
	    			$this->_checkoutSession->setIsAddToCartFailed(true);
	    			$observer->getRequest()->setParam('product', false);
					return $this;
	    		}
	    	}
        } 
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
}