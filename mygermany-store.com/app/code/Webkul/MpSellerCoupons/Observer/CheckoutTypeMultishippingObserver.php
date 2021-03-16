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
 * Webkul MpSellerCoupons CheckoutTypeMultishippingObserver Observer.
 */
class CheckoutTypeMultishippingObserver implements ObserverInterface
{

    /**
     * checkout_type_multishipping_create_orders_single event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $address = $observer->getEvent()->getAddress();
        $order->setData('coupondiscount_total', $address->getData('coupondiscount_total'));
        $order->save();
    }
}
