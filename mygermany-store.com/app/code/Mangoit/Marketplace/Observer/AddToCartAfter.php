<?php

/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Address;

class AddToCartAfter implements ObserverInterface 
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $_addressRepository;

    protected $_objectManager;
    protected $_customer;
    protected $_customerSession; 
    protected $_checkoutSession;
    protected $_messageManager;
    protected $cart;
    
    public function __construct(ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Customer $_customer,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_addressRepository = $addressRepository;
        $this->_objectManager = $objectmanager;
        $this->_customer = $_customer;
        $this->_customerSession = $_customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->cart = $cart;
    }
    
    /**
     * 
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) 
    {
        $quoteItems = $observer->getEvent()->getQuoteItem();
        $restrict = false;
        $itemsColl = $this->cart->getItems();
        // print_r(get_class_methods($itemsColl));
        // print_r($itemsColl->count());
        // die('died');
        // // print_r(get_class_methods());
        $productSku = $quoteItems->getProduct()->getSku();
        if ($productSku == 'wk_mp_ads_plan') {
            if ($restrict) {
            foreach ($itemsColl as $value) {
                if ($value->getSku() !=  $productSku) {
                    $value->delete();
                    $value->save();
                    $restrict = true;
                }
            }
                $this->_messageManager->addError(__("You cannot add Advertisement product with the other products!"));
            }
        } else {
            foreach ($itemsColl as $value) {
                if ($value->getSku() ==  'wk_mp_ads_plan') {
                    $value->delete();
                    $value->save();
                    $restrict = true;
                }
            }
            if ($restrict) {
                $this->_messageManager->addError(__("You cannot add other products with the Advertisement products!"));
            }
        }
    }    
}