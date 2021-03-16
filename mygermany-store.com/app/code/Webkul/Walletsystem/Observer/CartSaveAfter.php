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
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\Quote\ItemFactory;

class CartSaveAfter implements ObserverInterface
{
    /**
     * @var orderFactory
     */
    protected $_orderFactory;
    /**
     * @var itemFactory
     */
    protected $_itemFactory;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
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
     * @param OrderFactory                                $orderFactory
     * @param ItemFactory                                 $itemFactory
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Framework\App\Request\Http         $request
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface    $response
     */
    public function __construct(
        OrderFactory $orderFactory,
        ItemFactory $itemFactory,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_itemFactory = $itemFactory;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_response = $response;
    }
    /**
     * cart save after observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $event = $observer->getQuoteItem();
        $params = $this->_request->getParams();
        $minimumAmount = $this->_helper->getMinimumAmount();
        $maximumAmount = $this->_helper->getMaximumAmount();
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
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
        $itemId = '';
        $itemIds = '';
        $price = '';
        $flag = 0;
        $walletInCart = 0;
        $otherInCart = 0;
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $this->_helper->getCurrentCurrencyCode()
        );
        $controllerAction = $this->_request->getActionName();
        if ($controllerAction == 'reorder') {
            $orderId = $params['order_id'];
            $order = $this->_orderFactory->create()->load($orderId);
            if (count($cartData)) {
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $itemIds = $cart->getItemId();
                        $price = $cart->getCustomPrice();
                        $walletInCart = 1;
                    } else {
                        $otherInCart = 1;
                        if ($walletInCart == 1) {
                            $itemIds = $cart->getItemId();
                            break;
                        }
                    }
                }
            }
            if ($otherInCart == 1 && $walletInCart == 1) {
                $this->_messageManager->addNotice(
                    __(
                        'You can not add other products with wallet product, and vise versa.'
                    )
                );
                $quote = $this->_itemFactory->create()->load($itemIds);
                $quote->delete();
            } else {
                $this->reorderWalletProductAdd($cartData, $itemIds, $price, $finalmaximumAmount, $finalminimumAmount);
            }
        } else {
            if (array_key_exists('product', $params)) {
                $this->singleProductAdded($cartData, $params, $finalmaximumAmount, $finalminimumAmount);
            }
        }
        $this->_checkoutSession->getQuote()->save();
    }

    public function updateWalletProductItem($cart, $itemId, $price, $finalmaximumAmount, $finalminimumAmount)
    {
        $flag = 0;
        $currencySymbol = $this->_helper->getCurrencySymbol(
            $this->_helper->getCurrentCurrencyCode()
        );
        if ($cart->getItemId() != $itemId) {
            $amount = $price + $cart->getCustomPrice();
            if ($amount > $finalmaximumAmount) {
                $amount = $finalmaximumAmount;
                $this->_messageManager->addNotice(
                    __(
                        'You can not add more than %1 amount to your wallet.',
                        $currencySymbol.$finalmaximumAmount
                    )
                );
            } elseif ($amount < $finalminimumAmount) {
                $amount = $finalminimumAmount;
                $this->_messageManager->addNotice(
                    __(
                        'You can not add less than %1 amount to your wallet.',
                        $currencySymbol.$finalminimumAmount
                    )
                );
            }
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
            $this->_checkoutSession->getQuote()->setItemsQty(1);
            $this->_checkoutSession->getQuote()->setSubtotal($amount);
            $this->_checkoutSession->getQuote()->setGrandTotal($amount);
            $flag = 1;
        }
        return $flag;
    }

    public function singleProductAdded($cartData, $params, $finalmaximumAmount, $finalminimumAmount)
    {
        $itemId = '';
        $price = 0;
        $flag = 0;
        $productId = $params['product'];
        $walletProductId = $this->_helper->getWalletProductId();
        foreach ($cartData as $cart) {
            $itemId = $cart->getItemId();
            $price = $cart->getCustomPrice();
        }
        foreach ($cartData as $cart) {
            if ($cart->getProductId() == $productId) {
                if ($productId == $walletProductId) {
                    $flag = $this->updateWalletProductItem(
                        $cart,
                        $itemId,
                        $price,
                        $finalmaximumAmount,
                        $finalminimumAmount
                    );
                }
            }
            if ($flag == 1) {
                break;
            }
        }
        if ($itemId != '' && $flag == 1) {
            $quote = $this->_itemFactory->create()->load($itemId);
            $quote->delete();
        }
    }

    public function reorderWalletProductAdd($cartData, $itemIds, $price, $finalmaximumAmount, $finalminimumAmount)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $flag = 0;
        foreach ($cartData as $cart) {
            if ($cart->getProduct()->getId() == $walletProductId) {
                $flag = $this->updateWalletProductItem(
                    $cart,
                    $itemIds,
                    $price,
                    $finalmaximumAmount,
                    $finalminimumAmount
                );
            }
            if ($flag == 1) {
                break;
            }
        }
        if ($itemIds != '' && $flag == 1) {
            $quote = $this->_itemFactory->create()->load($itemIds);
            $quote->delete();
        }
    }
}
