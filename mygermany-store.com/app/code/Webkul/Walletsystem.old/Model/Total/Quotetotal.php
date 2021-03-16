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

namespace Webkul\Walletsystem\Model\Total;

class Quotetotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $_quoteValidator = null;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutsession;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @param \Magento\Framework\Model\Context    $context
     * @param \Magento\Checkout\Model\Session     $checkoutsession
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Webkul\Walletsystem\Helper\Data    $walletHelper
     * @param \Magento\Checkout\Helper\Cart       $cartHelper
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        $this->setCode('wallet');
        $this->_quoteValidator = $quoteValidator;
        $this->_checkoutsession = $checkoutsession;
        $this->_walletHelper = $walletHelper;
        $this->_cartHelper = $cartHelper;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }
        $helper = $this->_walletHelper;
        $helper->checkWalletproductWithOtherProduct();
        $address = $shippingAssignment->getShipping()->getAddress();
        $grandTotal = array_sum($total->getAllTotalAmounts());
        $balance = $this->getWalletamountForCart($grandTotal);
        if ($balance) {
            $currentCurrencyCode = $helper->getCurrentCurrencyCode();
            $baseCurrencyCode = $helper->getBaseCurrencyCode();
            $allowedCurrencies = $helper->getConfigAllowCurrencies();
            $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (empty($rates[$currentCurrencyCode])) {
                $rates[$currentCurrencyCode] = 1;
            }
            $baseAmount = $helper->baseCurrencyAmount($balance);
            $balance = -($balance);
            $baseAmount = -($baseAmount);
            $address->setData('wallet_amount', $balance);
            $address->setData('base_wallet_amount', $baseAmount);
            $total->setTotalAmount('wallet', $balance);
            $total->setBaseTotalAmount('wallet', $baseAmount);
            $quote->setWalletAmount($balance);
            $quote->setBaseWalletAmount($baseAmount);
            $total->setWalletAmount($balance);
            $total->setBaseWalletAmount($baseAmount);
        } else {
            $address->setData('wallet_amount', 0);
            $address->setData('base_wallet_amount', 0);
            $total->setTotalAmount('wallet', 0);
            $total->setBaseTotalAmount('wallet', 0);
            $quote->setWalletAmount(0);
            $quote->setBaseWalletAmount(0);
            $total->setWalletAmount(0);
            $total->setBaseWalletAmount(0);
        }
        return $this;
    }

    protected function getWalletamountForCart($addressGrandTotal)
    {
        $getSession = $this->_checkoutsession->getWalletDiscount();
        $wallethelper = $this->_walletHelper;
        $cartHelper = $this->_cartHelper;
        $amount = 0;
        $finalWalletAmount = 0;
        $grandtotal = 0;
        if (is_array($getSession) && array_key_exists('flag', $getSession) && $getSession['flag'] == 1) {
            $subtotal = $cartHelper->getQuote()->getSubtotal();
            $quote = $this->_checkoutsession->getQuote();
            $shippingAmount = $this->calculateShippingAmountFromQuote($quote);
            $cartDiscountamount = $this->getCartDiscountAmount();
            $totals = $this->_checkoutsession->getQuote()->getTotals();
            $taxAmount = $this->calculateTaxAmount($totals);
            $rewardAmount = $this->calculateRewardPointAmount($totals);
            $grandtotal = $subtotal + $shippingAmount + $taxAmount + $rewardAmount;
            if ($cartDiscountamount!=null) {
                $grandtotal = $grandtotal + $cartDiscountamount;
            }
            $grandtotal = round($grandtotal, 4);
            if ($getSession['grand_total'] != $grandtotal) {
                $getSession['grand_total'] = $grandtotal;
                $getSession['amount'] = 0;
                $getSession['type'] = 'reset';
                $this->_checkoutsession->setWalletDiscount($getSession);
                return 0;
            }
            $amount = $getSession['amount'];
            $finalWalletAmount = $getSession['amount'];
            if ($getSession['type'] == 'set') {
                $customerId = $wallethelper->getCustomerId();
                $totalAmount = $wallethelper->currentCurrencyAmount(
                    $wallethelper->getWalletTotalAmount($customerId),
                    ''
                );
                if ($getSession['amount'] > $grandtotal) {
                    $amount = $grandtotal;
                }
                if ($amount < $grandtotal) {
                    if ($grandtotal < $totalAmount) {
                        $amount = $grandtotal;
                    } else {
                        $amount = $totalAmount;
                    }
                }
                $walletPercent = ($amount*100)/$grandtotal;
                $finalWalletAmount = ($addressGrandTotal*$walletPercent)/100;
            }
        }

        return $finalWalletAmount;
    }
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => __('Wallet Amount'),
            'value' => $total->getWalletAmount(),
        ];
    }

    public function getLabel()
    {
        return __('Wallet Amount');
    }
    public function calculateDiscountAmount($quote)
    {
        $discountAmount = 0;
        $quoteItems = $quote->getAllItems();
        if (count($quoteItems)) {
            foreach ($quoteItems as $item) {
                $amount = $item->getDiscountAmount();
                $discountAmount = $discountAmount + $amount;
            }
        }
        return $discountAmount;
    }

    public function getCartDiscountAmount()
    {
        $cartDiscountamount = $this->_checkoutsession->getQuote()->getShippingAddress()->getDiscountAmount();
        if ($cartDiscountamount==null || $cartDiscountamount==0) {
            $cartDiscountamount = $this->_checkoutsession->getQuote()->getBillingAddress()->getDiscountAmount();
        }
        if ($cartDiscountamount==null || $cartDiscountamount==0) {
            $cartDiscountamount = $this->calculateDiscountAmount($this->_checkoutsession->getQuote());
            $cartDiscountamount = -1 * $cartDiscountamount;
        }
        return $cartDiscountamount;
    }

    public function calculateTaxAmount($totals)
    {
        $taxAmount = 0;
        if (array_key_exists('tax', $totals)) {
            $taxAmount = $totals['tax']->getValue();
        }
        if ($taxAmount == 0) {
            foreach ($this->_checkoutsession->getQuote()->getAllItems() as $item) {
                $taxAmount = $taxAmount + $item->getTaxAmount();
            }
        }
        return $taxAmount;
    }
    public function calculateShippingAmountFromQuote($quote)
    {
        $shippingAmount= 0;
        $allShippingAddress = $quote->getAllShippingAddresses();
        foreach ($allShippingAddress as $addresskey => $address) {
            $shippingAmount += $address->getShippingAmount();
        }
        return $shippingAmount;
    }
    public function calculateRewardPointAmount($totals)
    {
        $rewardAmount = 0;
        if (array_key_exists('reward_amount', $totals)) {
            $rewardAmount = $totals['reward_amount']->getValue();
        }
        return $rewardAmount;
    }
}
