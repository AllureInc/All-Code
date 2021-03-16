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

class Paymentmethodisactive implements ObserverInterface
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
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Webkul\Walletsystem\Helper\Data          $helper
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param \Magento\Framework\App\ResponseInterface  $response
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_response = $response;
    }
    /**
     * Payment method is active.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $paymentMethods = $this->_helper->getPaymentMethods();
        $paymentMethodArray = explode(',', $paymentMethods);
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $cardonly = false;
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $item) {
            if ($item->getProduct()->getId() == $walletProductId) {
                $cardonly = true;
            }
        }
        if (count($paymentMethods)) {
            if (!in_array($method->getCode(), $paymentMethodArray) && $cardonly == true) {
                $result = $observer->getEvent()->getResult();
                $result->setData('is_available', false);
            }
        }
    }
}
