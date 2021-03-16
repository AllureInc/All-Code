<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Account;

/**
 * MpAmazonConnector Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @return string current url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * getMpAuction for get complete url using url key
     * @param String $urlKey
     * @return String url
     */
    public function getDealUrl($urlKey)
    {
        return $this->getUrl(
            $urlKey,
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * getCurrentNavClass return nav item active or not class
     * @param  string $urlKey url key for match with current url
     * @return string|""
     */
    public function getCurrentNavClass($urlKey)
    {
        return strpos($this->getCurrentUrl(), $urlKey) ? "current" : "";
    }
}
