<?php

/**
 * Package © 2018 Mangoit_Marketplace.
 */

namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPredispatch implements ObserverInterface 
{
     /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    protected $_storeManager;
    private $url;
    protected $_messageManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect

    ) {
        $this->redirect = $redirect;
        $this->_storeManager = $storeManager;
        $this->quote = $checkoutSession->getQuote();
        $this->_messageManager = $messageManager;
        $this->url = $url;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // print_r(get_class_methods($this->_registry->registry('current_product')));
        $currentUrl = $this->_storeManager->getStore()->getCurrentUrl(false);
        $url = $this->url->getUrl('checkout/cart/index'); // give here your controller/action
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        if ($actionName == 'catalog_product_view' && (strpos($currentUrl, 'complete-preorder') !== false)) {
            $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
        } else if($actionName == 'checkout_cart_add') {
            $allItems = $this->quote->getAllItems();
            if (!empty($allItems)) {
                foreach ($allItems as $key => $value) {
                    if ($value->getSku() == 'preorder_complete') {
                        $observer->getRequest()->setParam('return_url', $this->redirect->getRefererUrl());
                        $this->_messageManager->addError(__('There is already a preorder product in the cart, please continue with that product.'));
                        die();
                    }
                }
            }
        }
    }
}