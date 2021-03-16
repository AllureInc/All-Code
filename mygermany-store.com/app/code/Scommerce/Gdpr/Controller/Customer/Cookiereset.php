<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Controller\Customer;

/**
 * Cookie Reset action from frontend
 *
 * Class Cookie Reset
 * @package Scommerce\Gdpr\Controller\Customer
 */
class Cookiereset extends \Magento\Framework\App\Action\Action
{
	/** @var \Magento\Framework\Stdlib\CookieManagerInterface */
	protected $_cookieManager;

	/** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory */
	protected $_cookieMetadataFactory;
	
	/** @var \Magento\Framework\Session\SessionManagerInterface */
	protected $_sessionManager;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;
	
	/** @var \Scommerce\Gdpr\Model\Service\QuoteReset */
    private $_quoteReset;
	
	/** @var \Scommerce\Gdpr\Model\Service\AnonymiseOrders */
    private $_anonymiseOrders;

    /** @var \Scommerce\CookiePopup\Helper\Data */
    private $_popupHelper;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService  */
    private $_choiceService;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Scommerce\Gdpr\Model\Service\QuoteReset $quoteReset
     * @param \Scommerce\Gdpr\Model\Service\AnonymiseOrders $anonymiseOrders
     * @param \Scommerce\Gdpr\Helper\Data $helper
     * @param \Scommerce\CookiePopup\Helper\Data $popupHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
		\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
		\Magento\Framework\Session\SessionManagerInterface $sessionManager,
		\Scommerce\Gdpr\Model\Service\QuoteReset $quoteReset,
		\Scommerce\Gdpr\Model\Service\AnonymiseOrders $anonymiseOrders,
        \Scommerce\Gdpr\Helper\Data $helper,
        \Scommerce\CookiePopup\Helper\Data $popupHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
    ) {
		$this->_cookieManager = $cookieManager;
		$this->_cookieMetadataFactory = $cookieMetadataFactory;
		$this->_sessionManager = $sessionManager;
		$this->helper = $helper;
		$this->_quoteReset = $quoteReset;
		$this->_anonymiseOrders = $anonymiseOrders;
		$this->_popupHelper = $popupHelper;
		$this->_choiceService = $choiceService;
		$this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
		$cookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
			->setDomain($this->_sessionManager->getCookieDomain())
			->setPath($this->_sessionManager->getCookiePath());

        if ($this->_popupHelper->isEnabled()) {
            $allCookies = $this->_choiceService->getStoreChoices($this->_storeManager->getStore()->getId());
            foreach ($allCookies->getItems() as $cookie) {
                /** @var $cookie \Scommerce\CookiePopup\Api\Data\ChoiceInterface */
                $this->_cookieManager->deleteCookie($cookie->getCookieName(), $cookieMetadata);
            }
        }

        $this->_cookieManager->deleteCookie($this->helper->getCookieAcceptedKey(), $cookieMetadata);
		$this->_cookieManager->deleteCookie($this->helper->getCookieClosedKey(), $cookieMetadata);
        $this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
}
