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
namespace Webkul\MpSellerCoupons\Model\Order\Invoice\Total;

class CouponDiscount extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect invoice subtotal
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order=$invoice->getOrder();
        $orderCoupondiscountTotal = $order->getCoupondiscountTotal();
        if ($orderCoupondiscountTotal && count($order->getInvoiceCollection())==0) {
            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderCoupondiscountTotal);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderCoupondiscountTotal);
        }
        return $this;
    }
}
