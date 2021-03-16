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

namespace Webkul\Walletsystem\Controller\Plugin\Store;

use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class SwitchAction
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $_helper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $_cartModel;
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $_request;
    /**
     * @var QuoteFactory
     */
    private $_quoteFactory;
    /**
     * @var StoreCookieManagerInterface
     */
    private $_storeCookieManager;
    /**
     * @var StoreRepositoryInterface
     */
    private $_storeRepository;

    /**
     * @param \Webkul\Walletsystem\Helper\Data           $helper
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Magento\Checkout\Model\Cart               $checkoutCartModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http        $request
     * @param QuoteFactory                               $quoteFactory
     * @param StoreCookieManagerInterface                $storeCookieManager
     * @param StoreRepositoryInterface                   $storeRepository
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $checkoutCartModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        QuoteFactory $quoteFactory,
        StoreCookieManagerInterface $storeCookieManager,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_cartModel = $checkoutCartModel;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_quoteFactory = $quoteFactory;
        $this->_storeCookieManager = $storeCookieManager;
        $this->_storeRepository = $storeRepository;
    }
    public function aroundExecute(
        \Magento\Store\Controller\Store\SwitchAction $subject,
        \Closure $proceed
    ) {
        $currentActiveStore = $this->_storeManager->getStore();
        $previousStorecode = $this->_storeCookieManager->getStoreCodeFromCookie();
        if ($previousStorecode==null) {
            $defaultStoreView = $this->_storeManager->getDefaultStoreView();
            $previousStorecode = $defaultStoreView->getCode();
        }
        $previousStore = $this->_storeRepository->getActiveStoreByCode($previousStorecode);
        $previousCurrency = $previousStore->getDefaultCurrencyCode();
        $currenctCurrency = $currentActiveStore->getCurrentCurrencyCode();

        $result = $proceed();

        if ($previousCurrency != $currenctCurrency) {
            $this->updateWalletAmountInCart($previousCurrency, $currenctCurrency);
        }
        return $result;
    }

    // wallet system changes start
    private function updateWalletAmountInCart($previousCurrency, $currenctCurrency)
    {
        $this->_checkoutSession->unsWalletDiscount();
        $walletProductId = $this->_helper->getWalletProductId();
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $this->_storeManager->getStore()->getCurrentCurrencyCode()
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
                            $previousCurrency,
                            $currenctCurrency,
                            $cart->getCustomPrice()
                        );
                        $finalminimumAmount = $this->_helper->getwkconvertCurrency(
                            $previousCurrency,
                            $currenctCurrency,
                            $minimumAmount
                        );
                        $finalmaximumAmount = $this->_helper->getwkconvertCurrency(
                            $previousCurrency,
                            $currenctCurrency,
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
            $this->_cartModel->save();
        }
    }
    //ends
    private function wkCartSave($cart, $finalPrice)
    {
        $cart->setPrice($finalPrice);
        $cart->setCustomPrice($finalPrice);
        $cart->setOriginalCustomPrice($finalPrice);
        $cart->setRowTotal($finalPrice);
        $cart->save();
    }
}
