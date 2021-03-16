<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Block;

/**
 * Class Link
 * @package Scommerce\CookiePopup\Block
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /** @var \Scommerce\CookiePopup\Helper\Data */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\CookiePopup\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->helper->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->helper->getMessageLinkText();
    }
}
