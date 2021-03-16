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
class CustomerLogin implements ObserverInterface
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    protected $cookieManager;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService */
    protected $choiceService;

    /** @var \Magento\Framework\Session\Config\ConfigInterface */
    protected $sessionConfig;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory */
    protected $cookieMetadataFactory;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * CustomerLogin constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->choiceService = $choiceService;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getData('customer');
        $choices = $this->choiceService->getCustomerChoices($customer->getId())->getItems();

        // Set cookies metadata
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath($this->sessionConfig->getCookiePath());
        $metadata->setDomain($this->sessionConfig->getCookieDomain());
        //$metadata->setDuration($this->sessionConfig->getCookieLifetime());
        $metadata->setDuration(60 * 60 * 24 * 365 * 100);
        $metadata->setSecure($this->sessionConfig->getCookieSecure());
        $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());

        // Restore Customer cookie choices
        foreach ($choices as $choice) {
            $value = $choice->getIsRequired() ? 1 : $choice->getStatus();
            $this->cookieManager->setPublicCookie($choice->getCookieName(), $value, $metadata);
        }
    }
}