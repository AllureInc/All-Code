<?php

/**
 * Package © 2018 Mangoit_Marketplace.
 */

namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddToCartCompleteAfter implements ObserverInterface 
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    protected $_objectManager;
    protected $cart;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Checkout\Model\Cart $cart
    )
    {
        $this->_objectManager = $objectmanager;
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
        $cartItems = $this->cart->getQuote()->getAllItems();
        foreach ($cartItems as $key => $value) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($value->getProductId());
            $fskType = $product->getFskProductType();
            if ($fskType) {
                $value->setFskProduct($fskType)->save();
            }
        }
    }    
}