<?php
namespace Mangoit\Marketplace\Model\Checkout\Cart;

use Magento\Framework\Exception\LocalizedException;

class Plugin
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quote = $checkoutSession->getQuote();
    }

    /**
     * beforeAddProduct
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $productInfo->setVisibility(4)->save();
        return [$productInfo, $requestInfo];
    }
}