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

namespace Webkul\Walletsystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\TotalsCollector;

class Applypaymentamount extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    protected $_collectTotal;

    /**
     * @param Context                      $context
     * @param WebkulWalletsystemHelperData $helper
     * @param MagentoCheckoutModelSession  $checkoutSession
     * @param PageFactory                  $resultPageFactory
     * @param MagentoCheckoutHelperCart    $cartHelper
     */
    public function __construct(
        Context $context,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Cart $cartHelper,
        TotalsCollector $collectTotal
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_cartHelper = $cartHelper;
        $this->_collectTotal = $collectTotal;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customerId = $this->_helper->getCustomerId();
        if (!$customerId) {
            $leftinWallet = $this->_helper->getformattedPrice(0);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'leftinWallet' => $leftinWallet,
            ];
            $this->_checkoutSession->setWalletDiscount($myValue);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($myValue);
            return $resultJson;
        }
        // $grandtotal = $this->getFinalGrandTotal($this->_checkoutSession->getQuote());
        $grandtotal = $this->_checkoutSession->getQuote()->getGrandTotal();
        $grandtotal = (float) $grandtotal;
        $grandtotal = round($grandtotal, 4);
        $customerId = $this->_helper->getCustomerId();
        $amount = $this->_helper->getWalletTotalAmount($customerId);
        $store = $this->_helper->getStore();
        $converttedAmount = $this->_helper->currentCurrencyAmount($amount, $store);
        if ($params['wallet'] == 'set') {
            if ($converttedAmount >= $grandtotal) {
                $discountAmount = $grandtotal;
            } else {
                $discountAmount = $converttedAmount;
            }
            $left = $converttedAmount - $discountAmount;
            $baseLeftAmount = $this->_helper->baseCurrencyAmount($left);
            $leftinWallet = $this->_helper->getformattedPrice(
                ($baseLeftAmount) > 0 ? $baseLeftAmount : 0
            );
            $myValue = [
                'flag' => 1,
                'amount' => $discountAmount,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->_checkoutSession->setWalletDiscount($myValue);
        } else {
            $leftinWallet = $this->_helper->getformattedPrice($amount);
            $myValue = [
                'flag' => 0,
                'amount' => 0,
                'type' => $params['wallet'],
                'grand_total' => $grandtotal,
                'leftinWallet' => $leftinWallet,
            ];
            $this->_checkoutSession->setWalletDiscount($myValue);
        }
        $this->_checkoutSession->getQuote()->collectTotals()->save();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($myValue);
        return $resultJson;
    }
    protected function getFinalGrandTotal($quote)
    {
        $grandTotal = 0;
        if (count($quote->getAllAddresses())) {
            foreach ($quote->getAllAddresses() as $address) {
                $addressTotal = $this->_collectTotal->collectAddressTotals($quote, $address);
                $grandTotal = $grandTotal + $addressTotal->getGrandTotal();
            }
        }
        return $grandTotal;
    }
}
