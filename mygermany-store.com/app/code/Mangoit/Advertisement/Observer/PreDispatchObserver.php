<?php

namespace Mangoit\Advertisement\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Checkout\Model\Session as CheckoutSession;

class PreDispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /** @var CheckoutSession */
    protected $checkoutSession;

    protected $_storeManager;

    protected $_directoryHelper;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect

    ) {
        $this->redirect = $redirect;
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->_currencyFactory = $currencyFactory;
        $this->_directoryHelper = $directoryHelper;
        $this->_customerSession = $customerSession;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();
        if($actionName == 'directory_currency_switch') {
            /** @var \Magento\Quote\Model\Quote  */
            $quote = $this->checkoutSession->getQuote();
            $curCurrency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
            $tarCurrency = $observer->getEvent()->getRequest()->getParam('currency');

            foreach ($quote->getAllVisibleItems() as $item) {
                if($item->getIsVirtual()){
                    $item->setCustomPrice($this->_directoryHelper->currencyConvert($item->getCustomBasePriceForAdvProduct(), "EUR", $tarCurrency));
                    $item->setOriginalCustomPrice($this->_directoryHelper->currencyConvert($item->getCustomBasePriceForAdvProduct(), "EUR", $tarCurrency));
                    $item->getProduct()->setIsSuperMode(true);
                    $quote->collectTotals()->save();
                }
            }
        }
    }

    public function convert($amountValue, $currencyCodeFrom = null, $currencyCodeTo = null)
    {
        /**
         * If is not specified the currency code from which we want to convert - use current currency
         */
        if (!$currencyCodeFrom) {
            $currencyCodeFrom = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        }

        /**
         * If is not specified the currency code to which we want to convert - use base currency
         */
        if (!$currencyCodeTo) {
            $currencyCodeTo = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        }

        /**
         * Do not convert if currency is same
         */
        if ($currencyCodeFrom == $currencyCodeTo) {
            return $amountValue;
        }

        /** @var float $rate */
        // Get rate
        $rate = $this->_currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
        // Get amount in new currency
        $amountValue = $amountValue * $rate;

        return $amountValue;
    }
    // public function currencyConvert($amount, $fromCurrency = null, $toCurrency = null)
    // {
    //   if(!$fromCurrency){
    //       $fromCurrency = $this->_storeManager->getStore()->getBaseCurrency();
    //   }

    //   if(!$toCurrency){
    //       $toCurrency = $this->_storeManager->getStore()->getCurrentCurrency();
    //   }

    //   if (is_string($fromCurrency)) {
    //       $rateToBase = $this->_currencyFactory->create()->load($fromCurrency)->getAnyRate($this->_storeManager->getStore()->getBaseCurrency()->getCode());
    //   } elseif ($fromCurrency instanceof \Magento\Directory\Model\Currency) {
    //       $rateToBase = $fromCurrency->getAnyRate($this->_storeManager->getStore()->getBaseCurrency()->getCode());
    //   }

    //   $rateFromBase = $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);

    //   if($rateToBase && $rateFromBase){
    //       $amount = $amount * $rateToBase * $rateFromBase;
    //   } else {
    //       throw new InputException(__('Please correct the target currency.'));
    //   }

    //   return $amount;
    // }
}
