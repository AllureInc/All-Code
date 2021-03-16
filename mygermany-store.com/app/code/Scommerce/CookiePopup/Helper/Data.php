<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Scommerce\CookiePopup\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLED                       = 'scommerce_cookie_popup/general/enable';

    const GENERAL_MODAL_TITLE           = 'scommerce_cookie_popup/general/modal_title';
    const GENERAL_BUTTON_TEXT           = 'scommerce_cookie_popup/general/button_text';
    const GENERAL_LIST_HEADER           = 'scommerce_cookie_popup/general/list_header';
    const GENERAL_LINK_TEXT             = 'scommerce_cookie_popup/general/link_text';
    const GENERAL_USE_GTM               = 'scommerce_cookie_popup/general/use_gtm';
    const GENERAL_USE_DATALAYERS        = 'scommerce_cookie_popup/general/use_datalayers';
    const GENERAL_SHOW_IF_NOT_ACCEPTED  = 'scommerce_cookie_popup/general/show_if_not_accepted';
    const GENERAL_ACCEPT_ALL_TEXT       = 'scommerce_cookie_popup/general/accept_all_text';
    const GENERAL_ACCEPT_BUTTON_TEXT    = 'scommerce_cookie_popup/general/accept_button_text';
    const GENERAL_MESSAGE_LINK_TEXT     = 'scommerce_cookie_popup/general/cookie_message_link_text';

    const STYLE_HEADER_BACKGROUND_COLOR = 'scommerce_cookie_popup/style/header_background_color';
    const STYLE_HEADER_FONT_COLOR       = 'scommerce_cookie_popup/style/header_font_color';
    const STYLE_FOOTER_BACKGROUND_COLOR = 'scommerce_cookie_popup/style/footer_background_color';
    const STYLE_FOOTER_FONT_COLOR       = 'scommerce_cookie_popup/style/footer_font_color';
    const STYLE_BORDER                  = 'scommerce_cookie_popup/style/border';
    const STYLE_BORDER_COLOR            = 'scommerce_cookie_popup/style/border_color';
    const STYLE_PREFIX                  = 'scommerce_cookie_popup/style/';

    const ADDITIONAL_FIRST_TAB_TITLE    = 'scommerce_cookie_popup/additional_tabs/first_tab_title';
    const ADDITIONAL_FIRST_TAB_TEXT     = 'scommerce_cookie_popup/additional_tabs/first_tab_text';
    const ADDITIONAL_MORE_INFO_TITLE    = 'scommerce_cookie_popup/additional_tabs/more_info_title';
    const ADDITIONAL_MORE_INFO_LINK     = 'scommerce_cookie_popup/additional_tabs/more_info_link';

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $gdprHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Scommerce\Gdpr\Helper\Data $gdprHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Scommerce\Gdpr\Helper\Data $gdprHelper
    ) {
        parent::__construct($context);
        $this->gdprHelper = $gdprHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Returns whether module is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::ENABLED) && $this->gdprHelper->isEnabled();
    }

    /**
     * Get title of modal window
     *
     * @return string
     */
    public function getModalTitle()
    {
        return $this->getValue(self::GENERAL_MODAL_TITLE);
    }

    /**
     * Get text on action button modal window
     *
     * @return string
     */
    public function getButtonText()
    {
        return $this->getValue(self::GENERAL_BUTTON_TEXT);
    }

    /**
     * Get Cookies List Header in modal window
     *
     * @return string
     */
    public function getListHeader()
    {
        return $this->getValue(self::GENERAL_LIST_HEADER);
    }

    /**
     * Get Cookie Settings Link Text
     *
     * @return string
     */
    public function getLinkText()
    {
        return $this->getValue(self::GENERAL_LINK_TEXT);
    }

    /**
     * Get Cookie notice message Link Text
     *
     * @return mixed
     */
    public function getMessageLinkText()
    {
        return $this->getValue(self::GENERAL_MESSAGE_LINK_TEXT);
    }

    /**
     * Is use GTM
     *
     * @return bool
     */
    public function isUseGtm()
    {
        return $this->isSetFlag(self::GENERAL_USE_GTM);
    }

    /**
     * Is use data layers
     *
     * @return bool
     */
    public function isUseDataLayers()
    {
        return $this->isSetFlag(self::GENERAL_USE_DATALAYERS);
    }

    /**
     * If use Data Layers enabled then Data Layers will be used instead of cookies for GTM
     *
     * @return bool
     */
    public function isGtmUsed()
    {
        return $this->isUseDataLayers() ? false : $this->isUseGtm();
    }

    /**
     * Get background color of modal window header
     *
     * @return string
     */
    public function getHeaderBackgroundColor()
    {
        return $this->getValue(self::STYLE_HEADER_BACKGROUND_COLOR);
    }

    /**
     * Get font color of modal window header
     *
     * @return string
     */
    public function getHeaderFontColor()
    {
        return $this->getValue(self::STYLE_HEADER_FONT_COLOR);
    }

    /**
     * Get background color of modal window footer
     *
     * @return string
     */
    public function getFooterBackgroundColor()
    {
        return $this->getValue(self::STYLE_FOOTER_BACKGROUND_COLOR);
    }

    /**
     * Get font color of modal window footer
     *
     * @return string
     */
    public function getFooterFontColor()
    {
        return $this->getValue(self::STYLE_FOOTER_FONT_COLOR);
    }

    /**
     * Returns boolean flag if style is set to "Yes" or "No"
     *
     * @param $style
     * @return bool
     */
    public function getStyleFlag($style)
    {
        return $this->isSetFlag(self::STYLE_PREFIX . $style);
    }

    /**
     * Returns style value
     *
     * @param $style
     * @return mixed
     */
    public function getStyleValue($style)
    {
        return $this->getValue(self::STYLE_PREFIX . $style);
    }

    /**
     * @return mixed
     */
    public function getFirstTabTitle()
    {
        return $this->getValue(self::ADDITIONAL_FIRST_TAB_TITLE);
    }

    /**
     * @return mixed
     */
    public function getFirstTabText()
    {
        return $this->getValue(self::ADDITIONAL_FIRST_TAB_TEXT);
    }

    /**
     * @return mixed
     */
    public function getMoreInfoTitle()
    {
        return $this->getValue(self::ADDITIONAL_MORE_INFO_TITLE);
    }

    /**
     * @return mixed
     */
    public function getMoreInfoLink()
    {
        return $this->getValue(self::ADDITIONAL_MORE_INFO_LINK);
    }

    /**
     * @return bool
     */
    public function hasFirstTab()
    {
        return $this->getValue(self::ADDITIONAL_FIRST_TAB_TITLE) && $this->getValue(self::ADDITIONAL_FIRST_TAB_TEXT);
    }

    /**
     * @return bool
     */
    public function hasLastTab()
    {
        return $this->getValue(self::ADDITIONAL_MORE_INFO_TITLE) && $this->getValue(self::ADDITIONAL_MORE_INFO_LINK);
    }

    /**
     * @return mixed
     */
    public function getSaveChoiceText()
    {
        return $this->getValue(self::GENERAL_BUTTON_TEXT);
    }

    /**
     * @return mixed
     */
    public function getAcceptAllText()
    {
        return $this->getValue(self::GENERAL_ACCEPT_ALL_TEXT);
    }

    /**
     * @return string returns
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
            'cookie_popup' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $fileName
     * @return string
     */
    public function getImageUrl($fileName)
    {
        return $this->getMediaUrl() . $fileName;
    }

    /**
     * Check if custom checkbox should be used
     *
     * @return bool
     */
    public function useCustomCheckbox()
    {
        return
            $this->getStyleFlag('custom_checkbox') &&
            $this->getStyleValue('custom_checkbox_on') &&
            $this->getStyleValue('custom_checkbox_off');
    }

    /**
     * @return bool
     */
    public function showIfNotAccepted()
    {
        return $this->isSetFlag(self::GENERAL_SHOW_IF_NOT_ACCEPTED);
    }

    /**
     * @return mixed
     */
    public function getAcceptButtonText()
    {
        return $this->getValue(self::GENERAL_ACCEPT_BUTTON_TEXT);
    }

    /**
     * @param null $params
     * @return string
     */
    public function getSaveUrl($params = null)
    {
        if ($params == null) {
            $params = ['_current' => true];
        } else {
            $params['_current'] = true;
        }
        return $this->_getUrl('scommerce_cookie_popup/choice/save', $params);
    }

    /**
     * @return string
     */
    public function getChoicesUrl()
    {
        return $this->_getUrl('scommerce_cookie_popup/choice/data');
    }

    /**
     * Serialize data depending Magento version
     *
     * @param mixed $data
     * @return string
     */
    public function serialize($data)
    {
        return $this->gdprHelper->serialize($data);
    }

    /**
     * Unserialize data depending Magento version
     *
     * @param string $data
     * @return mixed
     */
    public function unserialize($data)
    {
        return $this->gdprHelper->unserialize($data);
    }

    /**
     * Helper method for retrieve config value by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return mixed
     */
    private function getValue($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * Helper method for retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return bool
     */
    private function isSetFlag($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($path, $scopeType, $scopeCode);
    }
}
