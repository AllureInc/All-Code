<?php
/**
 * Walletsystem\Block cart.php
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block;

use Magento\Customer\Model\CustomerFactory;

class Cart extends \Magento\Checkout\Block\Cart
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrlBuilder;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var  CustomerFactory
     */
    protected $_customerModel;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url         $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart                    $cartHelper
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param \Webkul\Walletsystem\Helper\Data                 $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\Walletsystem\Helper\Data $helper,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
        $this->_cartHelper = $cartHelper;
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerModel = $customerFactory;
    }
    // check whether wallet product is added in cart or not
    public function getWalletInCart()
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $item) {
            if ($item->getProduct()->getId() == $walletProductId) {
                return true;
            }
        }
        return false;
    }
    // get wallet amount details of logged in customer
    public function getWalletDetailsData()
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $customerId = $this->_helper->getCustomerId();
        $customerName = $this->_customerModel
            ->create()
            ->load($customerId)
            ->getName();
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $this->_helper->getCurrentCurrencyCode()
        );
        $currentWalletamount = $this->_helper->getWalletTotalAmount($customerId);
        $currentWalletamount = $this->convertAmountToCurrent($currentWalletamount);
        $returnDataArray = [
            'customer_name' => $customerName,
            'wallet_amount' => $currentWalletamount,
            'walletProductId' => $walletProductId,
            'currencySymbol' => $currencySymbol
        ];
        return $returnDataArray;
    }
    // get item delete post json link
    public function getDeletePostJson($item)
    {
        return $this->_cartHelper->getDeletePostJson($item);
    }
    // get calculated credit rules data
    public function getCreditRuleData()
    {
        return $this->_helper->calculateCreditAmountforCart();
    }
    // get formatted price
    public function getFormattedPrice($price)
    {
        return $this->_helper->getFormattedPrice($price);
    }
    // convert amount to currency currency
    public function convertAmountToCurrent($amount)
    {
        $base = $this->_helper->getBaseCurrencyCode();
        $current = $this->_helper->getCurrentCurrencyCode();
        $returnamount = $this->_helper->getwkconvertCurrency($base, $current, $amount);
        return $returnamount;
    }
}
