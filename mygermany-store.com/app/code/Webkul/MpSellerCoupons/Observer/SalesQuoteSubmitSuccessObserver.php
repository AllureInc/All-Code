<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpSellerCoupons SalesQuoteSubmitSuccessObserver Observer.
 */
class SalesQuoteSubmitSuccessObserver implements ObserverInterface
{

    /**
     * sales_model_service_quote_submit_success event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getData('coupondiscount_total')) {
            $order->setData('coupondiscount_total', -$shippingAddress->getData('coupondiscount_total'));
            $order->setData('discount_amount', -abs($shippingAddress->getData('coupondiscount_total')));
        } else {
            $billingAddress = $quote->getBillingAddress();
            $order->setData('coupondiscount_total', -$billingAddress->getData('coupondiscount_total'));
            $order->setData('discount_amount', -abs($billingAddress->getData('coupondiscount_total')));
        }
        $order->save();
    }
}
