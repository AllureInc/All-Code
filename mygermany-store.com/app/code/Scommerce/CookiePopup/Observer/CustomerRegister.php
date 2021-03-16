<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Cookies
 * @package Scommerce\CookiePopup\Observer
 */
class CustomerRegister implements ObserverInterface
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    protected $cookieManager;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService */
    protected $choiceService;

    /** @var \Scommerce\CookiePopup\Helper\Data */
    protected $helper;

    /**
     * Cookies constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
     * @param \Scommerce\CookiePopup\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Scommerce\CookiePopup\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->choiceService = $choiceService;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getData('customer');
        $choices = $this->choiceService->getList()->getItems();
        $storeId = $this->storeManager->getStore()->getId();
        $customerId = $customer->getId();

        foreach ($choices as $choice) {
            /** @var $choice  */
            $value = $choice->getIsRequired() || $this->cookieManager->getCookie($choice->getCookieName()) == '1' ? 1 : 0;
            $this->choiceService->saveCustomerChoice($customerId, $choice->getChoiceId(), $storeId, $value);
        }
    }
}