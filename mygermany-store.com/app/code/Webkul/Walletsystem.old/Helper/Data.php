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

namespace Webkul\Walletsystem\Helper;

use Webkul\Walletsystem\Model\WalletcreditrulesFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\ObjectManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const WALLET_PRODUCT_SKU = 'wk_wallet_amount';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $_walletRecordFactory;
    /**
     * @var Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * @var Webkul\Walletsystem\Model\WalletcreditrulesFactory
     */
    protected $_walletcreditrulesFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var itemFactory
     */
    protected $_itemFactory;
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $_orderModel;
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;
    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    protected $cartModel;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    private $productRepository;

    private $productMetaData;

    protected $objectManager;
    
    /**
     * @param MagentoFrameworkAppHelperContext              $context
     * @param MagentoCustomerModelSession                   $customerSession
     * @param MagentoCheckoutModelSession                   $checkoutSession
     * @param MagentoFrameworkLocaleCurrencyInterface       $localeCurrency
     * @param MagentoStoreModelStoreManagerInterface        $storeManager
     * @param MagentoDirectoryModelCurrency                 $currency
     * @param MagentoFrameworkPricingPriceCurrencyInterface $priceCurrency
     * @param MagentoCatalogModelProductFactory             $productFactory
     * @param MagentoFrameworkPricingHelperData             $pricingHelper
     * @param WebkulWalletsystemModelWalletrecordFactory    $walletRecord
     * @param MagentoDirectoryHelperData                    $directoryHelper
     * @param WalletcreditrulesFactory                      $walletcreditrulesFactory
     * @param MagentoFrameworkStdlibDateTimeDateTime        $date
     * @param ItemFactory                                   $itemFactory
     * @param OrderFactory                                  $orderModel
     * @param WallettransactionFactory                      $walletTransaction
     * @param MagentoFrameworkTranslateInlineStateInterface $inlineTranslation
     * @param MagentoFrameworkMailTemplateTransportBuilder  $transportBuilder
     * @param MagentoCustomerModelCustomerFactory           $customerFactory
     * @param QuoteFactory                                  $quoteFactory
     * @param MagentoFrameworkMessageManagerInterface       $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\Walletsystem\Model\WalletrecordFactory $walletRecord,
        \Magento\Directory\Helper\Data $directoryHelper,
        WalletcreditrulesFactory $walletcreditrulesFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ItemFactory $itemFactory,
        OrderFactory $orderModel,
        WallettransactionFactory $walletTransaction,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QuoteFactory $quoteFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ProductMetadataInterface $productMetaData,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeCurrency = $localeCurrency;
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->_productFactory = $productFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_walletRecordFactory = $walletRecord;
        $this->_directoryHelper = $directoryHelper;
        $this->_walletcreditrulesFactory = $walletcreditrulesFactory;
        $this->_date = $date;
        $this->_itemFactory = $itemFactory;
        $this->_orderModel = $orderModel;
        $this->_walletTransaction = $walletTransaction;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerModel = $customerFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_messageManager = $messageManager;
        $this->cartModel = $cartModel;
        $this->_localeDate = $localeDate;
        $this->_stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->productMetaData = $productMetaData;
        $this->objectManager = $objectManager;
    }
    // return customer id from customer session
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    // return wallet amount is enabled or not
    public function getWalletenabled()
    {
        return  $this->scopeConfig->getValue(
            'payment/walletsystem/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // return  wallet amount product id
    public function getWalletProductId()
    {
        $walletProductId = $this->_productFactory->create()
            ->getIdBySku(self::WALLET_PRODUCT_SKU);

        return $walletProductId;
    }
    // return maximum amount set in system config
    public function getMaximumAmount()
    {
        return  $this->scopeConfig->getValue(
            'payment/walletsystem/maximumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // return minimum amount set in system config
    public function getMinimumAmount()
    {
        return  $this->scopeConfig->getValue(
            'payment/walletsystem/minimumamounttoadd',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // return payment methods selected in system config
    public function getPaymentMethods()
    {
        return  $this->scopeConfig->getValue(
            'payment/walletsystem/allowpaymentmethod',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // get price priority set in system config
    public function getPricePriority()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/general_settings/priority',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // get price type set insystem config
    public function getPriceType()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/general_settings/price_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // get system config transfer enable or not
    public function getTransferValidationEnable()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/transfer_settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // code validation duration
    public function getCodeValidationDuration()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/transfer_settings/duration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_admin_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_admin_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_customer_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_customer_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getOrderCreditPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_order_credit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getOrderDebitPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/prefix_order_debit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getcashbackprefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/cashback_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getrefundOrderPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/refund_order_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletOrderRefundPrefix()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/prefixfortransaction/refund_wallet_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getcronEnable()
    {
        return  $this->scopeConfig->getValue(
            'walletsystem/cron_jobs/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // return currency currency code
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    // get base currency code
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    // get all allowed currency in system config
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    // get currency rates
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    // get currency symbol of an currency code
    public function getCurrencySymbol($currencycode)
    {
        $currency = $this->_localeCurrency->getCurrency($currencycode);

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    // get formatted price according to currenct currency
    public function getformattedPrice($price)
    {
        return $this->_pricingHelper
            ->currency($price, true, false);
    }

    // get remaining total wallet amount of a customer
    public function getWalletTotalAmount($customerId)
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $amount = 0;
        $walletRecord = $this->_walletRecordFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecord->getSize()) {
            foreach ($walletRecord as $record) {
                $amount = $record->getRemainingAmount();
            }
        }

        return $amount;
    }

    public function getTotalWalletAmount($customerId)
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $amount = 0;
        $walletRecord = $this->_walletRecordFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecord->getSize()) {
            foreach ($walletRecord as $record) {
                $amount = $record->getTotalAmount();
            }
        }

        return $amount;
    }

    // check wallet amount is currently in use or not
    public function getWalletStatus()
    {
        $status = false;
        if ($this->_checkoutSession->getWalletDiscount()) {
            $walletSession = $this->_checkoutSession->getWalletDiscount();
            if (array_key_exists('type', $walletSession) && $walletSession['type'] == 'set') {
                $status = true;
            } else {
                $status = false;
            }
        }

        return $status;
    }
    // calculate grand total
    public function getGrandTotal()
    {
        $grandTotal = 0;
        $totals = $this->_checkoutSession->getQuote()->getTotals();
        if (array_key_exists('grand_total', $totals)) {
            $grandTotal = $totals['grand_total']->getValue();
        } else {
            $grandTotal = $this->_checkoutSession->getQuote()->getGrandTotal();
        }

        return $grandTotal;
    }
    public function getSubTotal()
    {
        $subTotal = 0;
        $totals = $this->_checkoutSession->getQuote()->getTotals();
        if (array_key_exists('subtotal', $totals)) {
            $subTotal = $totals['subtotal']->getValue();
        } else {
            $subTotal = $this->_checkoutSession->getQuote()->getSubtotal();
        }

        return $subTotal;
    }

    // check how much amount is left to pay
    public function getlefToPayAmount()
    {
        $grandTotal = 0;
        $grandTotal = $this->getGrandTotal();
        $leftamount = $grandTotal;
        if ($this->_checkoutSession->getWalletDiscount()) {
            $walletSession = $this->_checkoutSession->getWalletDiscount();
            if (array_key_exists('grand_total', $walletSession) && $walletSession['grand_total'] != $grandTotal) {
                $walletSession['grand_total'] = $grandTotal;
                $walletSession['amount'] = 0;
                $walletSession['type'] = 'reset';
                $this->_checkoutSession->setWalletDiscount($walletSession);
            }
            if (array_key_exists('amount', $walletSession)) {
                $leftamount = $grandTotal - $walletSession['amount'];
            }
        }
        return $leftamount;
    }

    // calculate how much amount is left in cart or not
    public function getLeftInWallet()
    {
        $leftinWallet = $this->getWalletTotalAmount($this->getCustomerId());
        if ($this->_checkoutSession->getWalletDiscount()) {
            $walletSession = $this->_checkoutSession->getWalletDiscount();
            if (is_array($walletSession) && array_key_exists('grand_total', $walletSession) && array_key_exists('type', $walletSession) && $walletSession['type'] == 'set' && $walletSession['grand_total']) {
                $leftinWallet = $leftinWallet - $walletSession['grand_total'];
            }
        }

        return $this->getformattedPrice($leftinWallet);
    }

    // check payment method is enable or not and is wallet product is in cart or not
    public function getPaymentisEnabled()
    {
        if ($this->getWalletenabled() && $this->getCustomerId()) {
            $walletProductId = $this->getWalletProductId();
            $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($cartData as $item) {
                if ($item->getProduct()->getId() == $walletProductId) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    // get admin default email id
    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // check whether wallet product is in cart or not
    public function getCartStatus()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $cart = $this->_checkoutSession
                ->getQuote()
                ->getAllItems();
            $productIds = [];
            $walletProductId = $this->getWalletProductId();
            if (count($cart)) {
                foreach ($cart as $item) {
                    $productIds[] = $item->getProduct()->getId();
                }
                if (count($productIds)) {
                    if (!in_array($walletProductId, $productIds)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // convert currency amount
    public function getwkconvertCurrency($fromCurrency, $toCurrency, $amount)
    {
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }

        if ($baseCurrencyCode==$toCurrency) {
            $currencyAmount = $amount/$rates[$fromCurrency];
        } else {
            $amount = $amount/$rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
        return $currencyAmount;
    }
    //get amount in base currency amount from current currency
    public function baseCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        if ($amount == 0) {
            return $amount;
        }
        $rate = $this->_priceCurrency->convert($amount, $store) / $amount;
        $amount = $amount / $rate;

        return round($amount, 4);
    }
    // convert amount according to currenct currency
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_directoryHelper
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }
    // get currenct store
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
    //get currenct currency amount from base
    public function currentCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        $returnAmount = $this->_priceCurrency->convert($amount, $store);

        return round($returnAmount, 4);
    }

    // url of controller in which apply wallet amount on order
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('walletsystem/index/applypaymentamount');
    }

    // Priority 0 product based,1 cart based
    public function calculateCreditAmountforCart($orderId = 0)
    {
        $walletProductId = $this->getWalletProductId();
        $creditAmount = 0;
        $priority = $this->getPricePriority();
        if ($orderId!=0) {
            $order = $this->_orderModel->create()->load($orderId);
        }
        if ($priority==0) {
            //product based
            if ($orderId!=0) {
                $cartData = $order->getAllItems();
            } else {
                $cartData = $this->cartModel->getQuote()->getAllItems();
            }
            foreach ($cartData as $item) {
                $price = 0;
                $qty = 0;
                if ($item->getProduct()->getId() == $walletProductId || $item->getParentItem()) {
                    continue;
                } else {
                    $productId = $item->getProduct()->getId();
                    $creditAmount += $this->getProductData($item);
                }
            }
        } else {
            //cart based
            $basedOn = 0;
            if ($orderId) {
                $amount = $order->getSubtotal();
            } else {
                $amount = $this->getSubTotal();                
            }
            $returnAmount = $this->getPriceBasedOnRules($basedOn, $amount);
            $priceType = $this->getPriceType();
            $creditAmount = $this->getPriceByType($priceType, $returnAmount, $amount);
        }
        if (!$creditAmount) {
            return 0;
        }
        return $creditAmount;
    }

    public function getProduct($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    public function getPriceBasedOnRules($type, $minimumAmount)
    {
        $today = $this->_date->gmtDate('Y-m-d');
        $amount = 0;
        $creditruleCollection = $this->_walletcreditrulesFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('based_on', $type)
            ->addFieldToFilter('start_date', ['lteq' => $today])
            ->addFieldToFilter('end_date', ['gteq' => $today])
            ->addFieldToFilter('minimum_amount', ['lteq'=>$minimumAmount])
            ->setOrder('amount', 'desc');

        if ($creditruleCollection->getSize()) {
            foreach ($creditruleCollection as $creditRule) {
                $amount = $creditRule->getAmount();
                break;
            }
        }
        return $amount;
    }

    public function getProductData($item)
    {
        $productId = $item->getProduct()->getId();
        $creditPrice = 0;
        $qty = 0;
        $creditPrice = $this->getProductCreditPrice($item->getProductId(), $item->getPrice());
        if ($item->getOrderId() && $item->getOrderId()!=0) {
            $qty = $item->getQtyOrdered();
        } else {
            $qty = $item->getQty();
        }
        $returnAmount = $creditPrice * $qty;
        return $returnAmount;
    }

    public function getProductCreditPrice($productId, $amount)
    {
        $product = $this->getProduct($productId);
        $price = 0;
        $productCreditAmountBasedOn = $product->getWalletCreditBasedOn();
        if ($productCreditAmountBasedOn == 1) {
            //product cash back amount
            $price = $product->getWalletCashBack();
        } elseif ($productCreditAmountBasedOn == 2) {
            //rule based
            $basedOn = 1;
            $price = $this->getPriceBasedOnRules($basedOn, $amount);
        }
        $priceType = $this->getPriceType();
        $returnPrice = $this->getPriceByType($priceType, $price, $amount);
        return $returnPrice;
    }

    public function getPriceByType($type, $price, $amount)
    {
        if ($type==0) {
            //fixed
            return $price;
        } else {
            //percent
            if ($price > 100) {
                $price = 100;
            }
            $percentAmount = ($amount*$price)/100;
            return $percentAmount;
        }
    }

    public function checkWalletproductWithOtherProduct()
    {
        $walletInCart = 0;
        $otherInCart = 0;
        $itemIds = '';
        $quote = '';
        if ($this->_checkoutSession->getQuoteId()) {
            $quoteId = $this->_checkoutSession->getQuoteId();
            $quote = $this->_quoteFactory->create()
                ->load($quoteId);
        }
        if ($quote) {
            $cartData = $quote->getAllVisibleItems();
            if (count($cartData)) {
                $walletProductId = $this->getWalletProductId();
                foreach ($cartData as $cart) {
                    if ($cart->getProduct()->getId() == $walletProductId) {
                        $itemIds = $cart->getItemId();
                        $price = $cart->getCustomPrice();
                        $walletInCart = 1;
                    } else {
                        $otherInCart = 1;
                    }
                }
                if ($walletInCart==1 && $otherInCart==1 && $itemIds!='') {
                    $quote = $this->_itemFactory->create()->load($itemIds);
                    $quote->delete();
                }
            }
        }
    }
    public function getCustomerByCustomerId($customerId)
    {
        return $this->_customerModel->create()->load($customerId);
    }

    public function getFormattedPriceAccToCurrency($amount, $precision, $currencyCode)
    {
        $precision = 2;
        return $this->_priceCurrency->format(
            $amount,
            $includeContainer = false,
            $precision,
            $scope = null,
            $currencyCode
        );
    }
    public function getTransactionPrefix($senderType, $action)
    {
        if ($senderType==0) {
            if ($action=='credit') {
                return $this->getOrderCreditPrefix();
            } else {
                return $this->getOrderDebitPrefix();
            }
        } elseif ($senderType==1) {
            return $this->getcashbackprefix();
        } elseif ($senderType==2) {
            if ($action=='credit') {
                return $this->getrefundOrderPrefix();
            } else {
                return $this->getWalletOrderRefundPrefix();
            }
        } elseif ($senderType==3) {
            if ($action=='credit') {
                return $this->getAdminCreditPrefix();
            } else {
                return $this->getAdminDebitPrefix();
            }
        } elseif ($senderType==4) {
            if ($action=='credit') {
                return $this->getCustomerCreditPrefix();
            } else {
                return $this->getCustomerDebitPrefix();
            }
        }
        return "wallet transaction";
    }

    public function validateScriptTag($string)
    {
        if ($string!='') {
            $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        }
        return $string;
    }
    public function getWalletRechargeTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/recharge_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletRechargeTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/recharge_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletUsedTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/used_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletUsedTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/used_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletCashbackTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/cashback_wallet_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletCashbackTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/cashback_wallet_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletAmountRefundTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/refund_wallet_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletAmountRefundTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/refund_wallet_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletOrderRefundTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/refund_order_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getWalletOrderRefundTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/refund_order_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminCreditAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/admin_credit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminCreditAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/admin_credit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminDebitAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/admin_debit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminDebitAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/admin_debit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerDebitAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/customer_debit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerDebitAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/customer_debit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerCreditAmountTemplateIdForCustomer()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/customer_credit_amount_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerCreditAmountTemplateIdForAdmin()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/customer_credit_amount_admin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCustomerAmountTransferOTPTemplateId()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/sendcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getMonthlystatementTemplateId()
    {
        return $this->scopeConfig->getValue(
            'walletsystem/email_template/monthlystatement',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function loadProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }
    public function convertStringAccToVersion($string, $type)
    {
        if ($string!='') {
            $magentoVersion = $this->productMetaData->getVersion();
            if (version_compare($magentoVersion, '2.2.0')) {
                if ($type=='encode') {
                    return json_encode($string);
                } else {
                    $object = json_decode($string);
                    if (is_object($object)) {
                        return json_decode(json_encode($object), true);
                    }
                    return $object;
                }
            } else {
                $serializeInterface = $this->objectManager->get('\Magento\Framework\Serialize\SerializerInterface');
                if ($type=='encode') {
                    return $serializeInterface->serialize($string);
                } else {
                    return $serializeInterface->unserialize($string);
                }
            }
        }
        return $string;
    }
    // get wallet amount details of logged in customer
    public function getWalletDetailsData()
    {
        $walletProductId = $this->getWalletProductId();
        $customerId = $this->getCustomerId();
        $customerName = $this->getCustomerByCustomerId($customerId)
            ->getName();
        $currencySymbol = $this->getCurrencySymbol(
            $this->getCurrentCurrencyCode()
        );
        $currentWalletamount = $this->getWalletTotalAmount($customerId);

        $base = $this->getBaseCurrencyCode();
        $current = $this->getCurrentCurrencyCode();
        $currentWalletamount = $this->getwkconvertCurrency($base, $current, $currentWalletamount);
        
        $returnDataArray = [
            'customer_name' => $customerName,
            'wallet_amount' => $currentWalletamount,
            'walletProductId' => $walletProductId,
            'currencySymbol' => $currencySymbol
        ];
        return $returnDataArray;
    }
}
