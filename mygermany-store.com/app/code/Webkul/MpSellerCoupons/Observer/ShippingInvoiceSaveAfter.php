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
 * Webkul MpSellerCoupons ShippingInvoiceSaveAfter Observer.
 */
class ShippingInvoiceSaveAfter implements ObserverInterface
{
    public function __construct(
        \Webkul\MpSellerCoupons\Model\MpSellerCoupons $sellerCouponModel
    ) {
        $this->_sellerCouponModel = $sellerCouponModel;
    }

    /**
     * mp_order_shipping_invoice_save_after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getInvoice();
        $orderId = $invoice->getOrderId();
        $couponData = $this->_sellerCouponModel->getCollection()
                                            ->addFieldToFilter('order_id', $orderId);
        if($couponData->getSize()) {
            $invoice->setDiscountAmount(0);
            $invoice->setBaseDiscountAmount(0);
            $invoice->setDiscountDescription('');
            $invoice->save();
        }
    }
}
