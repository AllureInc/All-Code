<?php
/**
 * Copyright © 2017 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Helper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreRepository;
use Webkul\Marketplace\Model\Sellertransaction;


class Corehelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $transport;
    protected $_objectManager;
    protected $scopeConfig; // for email
    protected $_request;    
    protected $_storeManager;    
    protected $_cart;    
    protected $_productloader;  
    protected $_sellerProducts;  
    protected $_customerSession;  
    protected $_customerRepositoryInterface;  
    protected $_addRepositoryInterface;  
    protected $_inlineTranslation; // for Email
    protected $transportBuilder; //for email
    protected $_checkoutSession;
    protected $_storeRepository;
    protected $salesList;
    protected $productFaq;
    protected $saleperpartner;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Sellertransaction
     */
    protected $sellertransaction;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */ 
    protected $_entityAttribute;

    /** @var \Magento\Sales\Api\Data\OrderInterface $order **/
    protected $order;
    
    public function __construct (
        \Magento\Framework\App\Helper\Context $context,
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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart ,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Webkul\Marketplace\Model\Product $_sellerProducts,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Customer\Api\AddressRepositoryInterface $_addRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        StoreRepository $storeRepository,
        Sellertransaction $sellertransaction,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Webkul\Marketplace\Model\Saleperpartner $saleperpartner,
        \Mangoit\VendorPayments\Helper\Data $helper,
        array $data = []
    ) {   
        $this->saleperpartner = $saleperpartner;
        $this->_request = $request;
        $this->_storeManager = $storeManager;   
        $this->_objectManager = $objectmanager;
        $this->_cart = $cart;  
        $this->_productloader = $_productloader;
        $this->_sellerProducts = $_sellerProducts;
        $this->_customerSession = $_customerSession;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->_addRepositoryInterface = $_addRepositoryInterface;
        $this->_inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeRepository = $storeRepository;
        $this->sellertransaction = $sellertransaction;
        $this->salesList = $salesList;
        $this->productFaq = $productFaq;
        $this->stockRegistry = $stockRegistry;
        $this->_entityAttribute = $entityAttribute;
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function adminEmailName()
    {
        return $this->helper->getConfigValue('trans_email/ident_general/name',$this->helper->getStore()->getStoreId());
    }
}