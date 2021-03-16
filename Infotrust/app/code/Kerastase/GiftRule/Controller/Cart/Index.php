<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */

namespace Kerastase\GiftRule\Controller\Cart;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Checkout\Controller\Cart\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var checkoutSession
     */    
    protected $checkoutSession;

    /**
     * @var $messageManager
     */
    protected $_messageManager; 

    /**
     * @var storeManager
     */
    protected $_storeManager; 

    const XML_PATH_FOR_RESTRICT_MESSAGE = "kerastase_giftrule/restric_add_to_cart/restrict_message";

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $resultPageFactory
        );
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {   
        
        $restrictMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_RESTRICT_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart'));
        if ($this->checkoutSession->getIsAddToCartFailed()) {
            $this->_messageManager->addError(_($restrictMessage));
            $this->checkoutSession->unsIsAddToCartFailed();
        }
        return $resultPage;
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
