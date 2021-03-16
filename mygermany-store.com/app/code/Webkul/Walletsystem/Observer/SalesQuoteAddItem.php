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
use Magento\Quote\Model\QuoteFactory;

class SalesQuoteAddItem implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @param OrderFactory                                  $orderFactory
     * @param \Magento\Framework\App\Request\Http           $request
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param \Webkul\Walletsystem\Helper\Data              $walletHelper
     * @param \Magento\Checkout\Model\Session               $checkoutSession
     * @param QuoteFactory                                  $quoteFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteFactory $quoteFactory
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_walletHelper = $walletHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteFactory = $quoteFactory;
    }
    /**
     * add quote item handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_walletHelper;
        $walletProductId = $helper->getWalletProductId();
        $amount = 0;
        $params = $this->_request->getParams();
        $minimumAmount = $helper->getMinimumAmount();
        $maximumAmount = $helper->getMaximumAmount();
        $currencySymbol = $helper->getCurrencySymbol($helper->getCurrentCurrencyCode());
        $event = $observer->getQuoteItem();
        $product = $event->getProduct();
        $productId = $product->getId();
        if ($minimumAmount > $maximumAmount) {
            $temp = $minimumAmount;
            $minimumAmount = $maximumAmount;
            $maximumAmount = $temp;
        }
        $currentCurrenyCode = $helper->getCurrentCurrencyCode();
        $baseCurrenyCode = $helper->getBaseCurrencyCode();

        $finalminimumAmount = $helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $minimumAmount
        );
        $finalmaximumAmount = $helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $maximumAmount
        );
        $controllerAction = $this->_request->getActionName();
        $walletAmountExists = $this->getExistsWalletPrice();
        if ($controllerAction == 'reorder') {
            $orderId = $params['order_id'];
            $order = $this->_orderFactory->create()->load($orderId);
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() == $productId) {
                    if ($productId == $walletProductId) {
                        $amount = $orderItem->getPrice();
                        $this->updateWalletProductAmount(
                            $amount,
                            $finalminimumAmount,
                            $finalmaximumAmount,
                            $event,
                            $walletAmountExists
                        );
                    }
                }
            }
        } else {
            if (array_key_exists('price', $params)) {
                $amount = $params['price'];
            }
            if ($productId == $walletProductId) {
                $this->updateWalletProductAmount(
                    $amount,
                    $finalminimumAmount,
                    $finalmaximumAmount,
                    $event,
                    $walletAmountExists
                );
            }
        }
    }

    public function updateWalletProductAmount(
        $amount,
        $finalminimumAmount,
        $finalmaximumAmount,
        $event,
        $walletAmountExists
    ) {
        $currencySymbol = $this->_walletHelper->getCurrencySymbol(
            $this->_walletHelper->getCurrentCurrencyCode()
        );
        $finalAmount = $walletAmountExists + $amount;
        $setAmount = $amount;
        if ($finalAmount > $finalmaximumAmount) {
            $setAmount = $finalmaximumAmount-$walletAmountExists;
            $this->_messageManager->addNotice(
                __(
                    'You can not add more than %1 amount to your wallet.',
                    $currencySymbol.$finalmaximumAmount
                )
            );
        } elseif ($finalAmount < $finalminimumAmount) {
            $setAmount = $finalminimumAmount+$walletAmountExists;
            $this->_messageManager->addNotice(
                __(
                    'You can not add less than %1 amount to your wallet.',
                    $currencySymbol.$finalminimumAmount
                )
            );
        }
        $event->setOriginalCustomPrice($setAmount);
        $event->setCustomPrice($setAmount);
        if (!$this->_walletHelper->getDiscountEnable()) {
            $event->setNoDiscount(1);
        } else {
            $event->setNoDiscount(0);
        }
        $event->getProduct()->setIsSuperMode(true);
    }
    public function getExistsWalletPrice()
    {
        $walletInCart = 0;
        $price = 0;
        $quote = '';
        if ($this->_checkoutSession->getQuoteId()) {
            $quoteId = $this->_checkoutSession->getQuoteId();
            $quote = $this->_quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote) {
            $cartData = $quote->getAllVisibleItems();
            if (count($cartData)) {
                $walletProductId = $this->_walletHelper->getWalletProductId();
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $price = $cart->getCustomPrice();
                    }
                }
            }
        }
        return $price;
    }
}
