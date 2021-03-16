<?php
/**
 * Copyright © 2017 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\AmazonIntegration\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_request;    
    protected $_storeManager;    
    protected $_cart;    
    protected $_productloader;  
    protected $_sellerProducts;  
    protected $_customerSession;  
    protected $_customerRepositoryInterface;  
    protected $_addRepositoryInterface;  
    protected $amazonAccounts;  
    protected $eavConfig;

    public function __construct (
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart ,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Webkul\Marketplace\Model\Product $_sellerProducts,
        \Webkul\AmazonMagentoConnect\Model\Accounts $amazonAccounts,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Customer\Api\AddressRepositoryInterface $_addRepositoryInterface,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {   
        $this->_request = $request;
        $this->_storeManager = $storeManager;   
        $this->_cart = $cart;  
        $this->_productloader = $_productloader;
        $this->_sellerProducts = $_sellerProducts;
        $this->amazonAccounts = $amazonAccounts;
        $this->_customerSession = $_customerSession;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->_addRepositoryInterface = $_addRepositoryInterface;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }


    public function getControllerModule()
    {
         return $this->_request->getControllerModule();
    }
        
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }
        
    public function getRouteName()
    {
         return $this->_request->getRouteName();
    }
        
    public function getActionName()
    {
         return $this->_request->getActionName();
    }
        
    public function getControllerName()
    {
         return $this->_request->getControllerName();
    }
        
    public function getModuleName()
    {
         return $this->_request->getModuleName();
    }   

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }   
    public function getAmazonAccount()
    {
        $amazonCollObj = $this->amazonAccounts->load($this->_customerSession->getCustomer()->getId(),'magento_seller_id');
        return $amazonCollObj;
    }
    public function getSelectAttributeOptions($attributeCode)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode)->getOptions();
        return $attribute;
    }
}