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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add Weee item to Payment Cart amount.
 */
class AddPaymentWalletAmountItem implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutsession;

    /**
     * @param Data $weeeData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->storeManager = $storeManager;
        $this->_checkoutsession = $checkoutsession;
        $this->helper = $walletHelper;
    }

    /**
     * Add wallet amount as custom item to payment cart totals.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();

        $getSession = $this->_checkoutsession->getWalletDiscount();
        if (is_array($getSession) && array_key_exists('amount', $getSession) && $getSession['amount']!=0) {
            $baseAmount = $this->helper->baseCurrencyAmount($getSession['amount']);
            $cart->addCustomItem(__('Wallet Amount'), 1, -1.00 * $baseAmount);
        }
    }
}
