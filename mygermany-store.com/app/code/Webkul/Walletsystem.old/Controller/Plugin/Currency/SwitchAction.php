<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Plugin\Currency;

use Magento\Quote\Model\QuoteFactory;

class SwitchAction
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @param WebkulWalletsystemHelperData           $helper
     * @param MagentoCheckoutModelSession            $checkoutSession
     * @param MagentoCheckoutModelCart               $checkoutCartModel
     * @param MagentoStoreModelStoreManagerInterface $storeManager
     * @param MagentoFrameworkAppRequestHttp         $request
     * @param QuoteFactory                           $quoteFactory
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $checkoutCartModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        QuoteFactory $quoteFactory
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_cartModel = $checkoutCartModel;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_quoteFactory = $quoteFactory;
    }
    public function aroundExecute(
        \Magento\Directory\Controller\Currency\SwitchAction $subject,
        \Closure $proceed
    ) {
        $this->_checkoutSession->unsWalletDiscount();
        $storeManager = $this->_storeManager;
        $prevCurrency = $storeManager->getStore()->getCurrentCurrencyCode();
        $result = $proceed();
        $currency = (string) $this->_request->getParam('currency');
        $walletProductId = $this->_helper->getWalletProductId();
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $storeManager->getStore()->getCurrentCurrencyCode()
        );
        $quote = '';
        if ($this->_checkoutSession->getQuoteId()) {
            $quoteId = $this->_checkoutSession->getQuoteId();
            $quote = $this->_quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote!='') {
            $cartData = $quote->getAllVisibleItems();
            if (count($cartData)) {
                foreach ($cartData as $cart) {
                    if ($cart->getProductId() == $walletProductId) {
                        $minimumAmount = $this->_helper->getMinimumAmount();
                        $maximumAmount = $this->_helper->getMaximumAmount();
                        if ($minimumAmount > $maximumAmount) {
                            $temp = $maximumAmount;
                            $maximumAmount = $minimumAmount;
                            $minimumAmount = $temp;
                        }
                        $finalPrice = $this->_helper->getwkconvertCurrency(
                            $prevCurrency,
                            $currency,
                            $cart->getCustomPrice()
                        );
                        $finalminimumAmount = $this->_helper->getwkconvertCurrency(
                            $prevCurrency,
                            $currency,
                            $minimumAmount
                        );
                        $finalmaximumAmount = $this->_helper->getwkconvertCurrency(
                            $prevCurrency,
                            $currency,
                            $maximumAmount
                        );
                        if ($finalPrice > $finalmaximumAmount) {
                            $finalPrice = $finalmaximumAmount;
                        } elseif ($finalPrice < $finalminimumAmount) {
                            $finalPrice = $finalminimumAmount;
                        }
                        $this->wkCartSave($cart, $finalPrice);
                    }
                }
            }
        }
        $this->_cartModel->save();
        return $result;
    }

    public function wkCartSave($cart, $finalPrice)
    {
        $cart->setPrice($finalPrice);
        $cart->setCustomPrice($finalPrice);
        $cart->setOriginalCustomPrice($finalPrice);
        $cart->setRowTotal($finalPrice);
        $cart->save();
    }
}
