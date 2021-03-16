<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Choice;

/**
 * Class Save
 * @package Scommerce\CookiePopup\Controller\Choice
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Customer\Model\Session */
    private $session;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService */
    private $choiceService;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    protected $cookieManager;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory */
    protected $cookieMetadataFactory;

    /** @var \Magento\Framework\Session\Config\ConfigInterface */
    protected $sessionConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Scommerce\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Scommerce\Gdpr\Helper\Data $helper,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
    ) {
        $this->session = $session;
        $this->helper = $helper;
        $this->choiceService = $choiceService;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $this->getRequest();

        $saveAll = $request->getParam('save_all') == '1';

        $storeId = $this->storeManager->getStore()->getId();
        $allChoices = $this->choiceService->getStoreChoices($storeId)->getItems();

        // Set cookies metadata
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath($this->sessionConfig->getCookiePath());
        $metadata->setDomain($this->sessionConfig->getCookieDomain());
        //$metadata->setDuration($this->sessionConfig->getCookieLifetime());
        $metadata->setDuration(60 * 60 * 24 * 365 * 100);
        $metadata->setSecure($this->sessionConfig->getCookieSecure());
        $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());

        // Set cookies
        foreach ($allChoices as $choice) {
            if ($saveAll || $request->getParam($choice->getCookieName()) == 'on' || $choice->getIsRequired()) {
                $this->cookieManager->setPublicCookie($choice->getCookieName(), "1", $metadata);
                $this->saveCustomerCookieChoice($choice->getChoiceId(), $storeId, 1);
            }
            else {
                $this->cookieManager->setPublicCookie($choice->getCookieName(), "0", $metadata);
                $this->saveCustomerCookieChoice($choice->getChoiceId(), $storeId, 0);
            }
        }

        // Redirect back to previous page
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Save customer choice with the customer
     *
     * @param $cookieId
     * @param $storeId
     * @param $value
     */
    private function saveCustomerCookieChoice($cookieId, $storeId, $value)
    {
        if ($this->session->isLoggedIn()) {
            $this->choiceService->saveCustomerChoice($this->session->getCustomerId(), $cookieId, $storeId, $value);
        }
    }
}
