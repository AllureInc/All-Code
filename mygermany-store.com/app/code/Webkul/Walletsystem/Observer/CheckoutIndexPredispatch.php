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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutIndexPredispatch implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface    $response
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_response = $response;
        $this->_storeManager = $storeManager;
    }
    /**
     * checkout index predispatch observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $minimumAmount = $this->_helper->getMinimumAmount();
        $maximumAmount = $this->_helper->getMaximumAmount();
        $cartData = $this->_checkoutSession->getQuote()->getAllItems();
        if ($minimumAmount > $maximumAmount) {
            $temp = $minimumAmount;
            $minimumAmount = $maximumAmount;
            $maximumAmount = $temp;
        }
        $currentCurrenyCode = $this->_helper->getCurrentCurrencyCode();
        $baseCurrenyCode = $this->_helper->getBaseCurrencyCode();

        $finalminimumAmount = $this->_helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $minimumAmount
        );
        $finalmaximumAmount = $this->_helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $maximumAmount
        );
        $amount = 0;
        $itemId = '';
        $itemIds = '';
        $price = '';
        $flag = 0;
        $walletInCart = 0;
        $otherInCart = 0;
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $this->_helper->getCurrentCurrencyCode()
        );
        foreach ($cartData as $cart) {
            if ($cart->getProduct()->getId() == $walletProductId) {
                $amount = $cart->getCustomPrice();
                if ($amount > $finalmaximumAmount) {
                    $amount = $finalmaximumAmount;
                    $flag = 1;
                    $this->_messageManager->addNotice(
                        __(
                            'You can not add more than %1 amount to your wallet.',
                            $currencySymbol.$finalmaximumAmount
                        )
                    );
                } elseif ($amount < $finalminimumAmount) {
                    $amount = $finalminimumAmount;
                    $flag = 1;
                    $this->_messageManager->addNotice(
                        __(
                            'You can not add less than %1 amount to your wallet.',
                            $currencySymbol.$finalminimumAmount
                        )
                    );
                }
                if ($flag == 1) {
                    $this->cartUpdate($cart, $amount);
                    $this->_checkoutSession->getQuote()->setItemsQty(1);
                    $this->_checkoutSession->getQuote()->setSubtotal($amount);
                    $this->_checkoutSession->getQuote()->setGrandTotal($amount);
                    $storeManager = $this->_storeManager;
                    $currentStore = $storeManager->getStore();
                    $url = $currentStore->getBaseUrl().'checkout/index/';
                    $this->_response->setRedirect($url)->sendResponse();
                } else {
                    if (!$this->_helper->getDiscountEnable()) {
                        $cart->setNoDiscount(1);
                    } else {
                        $cart->setNoDiscount(0);
                    }
                    $cart->save();
                }
            }
        }
        $this->_checkoutSession->getQuote()->save();
    }

    public function cartUpdate($cart, $amount)
    {
        $cart->setCustomPrice($amount);
        $cart->setOriginalCustomPrice($amount);
        $cart->setQty(1);
        $cart->getProduct()->setIsSuperMode(true);
        $cart->setRowTotal($amount);
        if (!$this->_helper->getDiscountEnable()) {
            $cart->setNoDiscount(1);
        } else {
            $cart->setNoDiscount(0);
        }
        $cart->save();
    }
}
