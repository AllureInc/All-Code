<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Block;

/**
 * Class Cookienotice
 * @package Scommerce\Gdpr\Block
 */
class Cookienotice extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    private $cookieManager;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService */
    private $choiceService;

    /** @var \Scommerce\CookiePopup\Helper\Data */
    private $popupHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /**
     * Cookienotice constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
     * @param \Scommerce\CookiePopup\Helper\Data $popupHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Scommerce\Gdpr\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Scommerce\CookiePopup\Helper\Data $popupHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Scommerce\Gdpr\Helper\Data $helper,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        $this->helper = $helper;
        $this->choiceService = $choiceService;
        $this->popupHelper = $popupHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Is blocked enable
     *
     * @return bool
     */
    public function isBlocked()
    {
        return $this->helper->isBlocked();
    }

    /**
     * Is message position set to bottom
     *
     * @return bool
     */
    public function isBottom()
    {
        return $this->helper->isCookieMessagePositionBottom();
    }

    /**
     * @return string
     */
    public function getCssPageWrapperClass()
    {
        return $this->helper->getCssPageWrapperClass();
    }

    /**
     * @return string
     */
    public function getCmsPageUrl()
    {
        return $this->helper->getCmsPageUrl();
    }

    /**
     * @return string
     */
    public function getCookieTextMessage()
    {
        return $this->helper->getCookieTextMessage();
    }

    /**
     * @return string
     */
    public function getCookieLinkText()
    {
        return $this->helper->getCookieLinkText();
    }

    /**
     * @return string
     */
    public function getCookieTextColor()
    {
        return $this->helper->getCookieTextColor();
    }

    /**
     * @return string
     */
    public function getCookieBackgroundColor()
    {
        return $this->helper->getCookieBackgroundColor();
    }

    /**
     * @return string
     */
    public function getCookieLinkColor()
    {
        return $this->helper->getCookieLinkColor();
    }

    /**
     * Get cookie key to check accepted cookie policy
     *
     * @return string
     */
    public function getCookieKey()
    {
        return $this->helper->getCookieAcceptedKey();
    }

    /**
     * Get cookie key to check if cookie message was closed
     *
     * @return string
     */
    public function getCookieClosedKey()
    {
        return $this->helper->getCookieClosedKey();
    }

    /**
     * Check if has cookie with accepted cookie policy
     *
     * @return bool
     */
    public function hasCookie()
    {
        return $this->cookieManager->getCookie($this->getCookieKey()) !== null;
    }

    /**
     * @return \Scommerce\CookiePopup\Helper\Data
     */
    public function getPopupHelper()
    {
        return $this->popupHelper;
    }

    /**
     * Show block only if:
     *  a. Module enabled
     *  b. Cookie enabled
     *  c. Cookie doesn't set yet
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->popupHelper->isEnabled()) {
            return ($this->helper->isEnabled() && $this->helper->isCookieEnabled()) ?
                parent::_toHtml() : '';
        }

        return ($this->helper->isEnabled() && $this->helper->isCookieEnabled()) ?
            parent::_toHtml() : '';
    }

    /**
     * @param $choices
     * @return bool
     */
    private function allCookiesAreSet($choices)
    {
        foreach ($choices as $choice) {
            $this->_logger->debug($choice->getChoiceName());
            if ($this->cookieManager->getCookie($choice->getCookieName($choice->getCookieName())) === null ||
                $this->cookieManager->getCookie($choice->getCookieName($choice->getCookieName())) === '0'
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function shouldShowNotice()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $choices = $this->choiceService->getStoreChoices($storeId)->getItems();

        if (!$this->popupHelper->showIfNotAccepted() || count($choices) == 0) {
            if (!$this->hasCookie()) return true;
        } else {
            if (!$this->allCookiesAreSet($choices)) return true;
        }
        return false;
    }

    /**
     * @return int|void
     */
    public function hasChoices()
    {
        return count($this->choiceService->getStoreChoices()->getItems());
    }

    public function getStylesConfig()
    {
        $stylesConfig = [
            "cookieBackgroundColor" => '#' . $this->getCookieBackgroundColor(),
            "cookieTextColor"       => '#' . $this->getCookieTextColor()
        ];

        return json_encode($stylesConfig);
    }
}
