<?php

namespace Cnnb\AlgoliaFix\Controller\Guestwishlist;

use Magento\Framework\Controller\ResultFactory;
use Cnnb\AlgoliaFix\Helper\AlgoliaFixHelper;
use Magento\Store\Model\ScopeInterface;

class Add extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var MGS\Guestwishlist
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory; 

    /**
     * @var \Cnnb\AlgoliaFix\Helper\AlgoliaFixHelper
     */
    protected $_algoliaFixHelper; 

    /**
     * @var \Cnnb\AlgoliaFix\Helper\AlgoliaFixHelper
     */
    protected $_customerSession;

    /**
     * @var $messageManager
     */
    protected $_messageManager;

    protected $_wishlistFactory;
    protected $_wishlistResource;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
     * @param \MGS\Guestwishlist\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Cnnb\AlgoliaFix\Helper\AlgoliaFixHelper $algoliaFixHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MGS\Guestwishlist\Helper\Data $helper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        AlgoliaFixHelper $algoliaFixHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlistResource
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->_helper = $helper;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_algoliaFixHelper = $algoliaFixHelper;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_wishlistFactory  = $wishlistFactory;
        $this->_wishlistResource = $wishlistResource;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $requestParams = $this->getRequest()->getParams();
        
        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }
        
        $isModuleEnable = $this->isModuleEnable();
        if (!$isModuleEnable) {
            $isCustomerLoggedin = $this->_customerSession->isLoggedIn();
            if($isCustomerLoggedin)
            {
                $product = $this->productRepository->getById($productId);
                $customerId = $this->_customerSession->getCustomer()->getId();
                 //load wishlist by customer id
                $wishlist = $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
                $wishlist->addNewItem($product);
                //save wishlist
                $this->_wishlistResource->save($wishlist);
            } else {
                $this->_messageManager->addError('You must login or register to add items to your wishlist.');
                $resultRedirect->setPath('customer/account/login/');
                return $resultRedirect;   
            }
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
        }

        try {
            $itemId = $this->_helper->getRandomString();
            $item = [
                'item_id' => $itemId,
                'qty' => 1,
                'info_buyRequest' => $requestParams
            ];
            if ($product->getTypeId() === 'configurable' && isset($requestParams['super_attribute']) && is_array($requestParams['super_attribute'])) {
                $item['super_attribute'] = $requestParams['super_attribute'];
            }
            $cookie = $this->_helper->getCookie(\MGS\Guestwishlist\Helper\Data::COOKIE_NAME);
            /*
             * check existing product with same options
             * if yes, we don't need add to wishlist
             */
            if (!$this->_helper->checkExistItem($productId, $item, $cookie)) {
                $cookie[$productId][$itemId] = $item;
                $metadata = $this->_cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setPath('/')
                        ->setDuration(86400);
                $this->_cookieManager->setPublicCookie(
                    \MGS\Guestwishlist\Helper\Data::COOKIE_NAME,
                    serialize($cookie),
                    $metadata
                );
            }
            $this->messageManager->addComplexSuccessMessage(
                'addProductSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'referer' => $product->getProductLinks()
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * 
     * @return int|boolean
     */
    public function isModuleEnable() {
        return $this->_scopeConfig->isSetFlag('guestwishlist/additional/enable_module', ScopeInterface::SCOPE_STORE);
    }
}
