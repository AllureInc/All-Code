<?php
/**
 * Wallet system block Checkout.php
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block;

use Magento\Quote\Model\QuoteFactory;

class Checkout extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;
    /**
     * @var QuoteFactory
     */
    private $_quoteFactory;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Walletsystem\Helper\Data                 $walletHelper
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_walletHelper = $walletHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteFactory = $quoteFactory;
    }
    
    // get url on which ajax has been sent for setting wallet amount
    public function getAjaxUrl()
    {
        return $this->_walletHelper->getAjaxUrl();
    }
    // get grandtotal from quote data
    public function getGrandTotal()
    {
        $quote = '';
        if ($this->_checkoutSession) {
            if ($this->_checkoutSession->getQuoteId()) {
                $quoteId = $this->_checkoutSession->getQuoteId();
                $quote = $this->_quoteFactory->create()
                    ->load($quoteId);
            }
        }
        if ($quote) {
            $quoteData = $quote->getData();
            if (is_array($quoteData)) {
                if (array_key_exists('grand_total', $quoteData)) {
                    return $grandTotal = $quoteData['grand_total'];
                } else {
                    return 0;
                }
            }
        }
    }
}
