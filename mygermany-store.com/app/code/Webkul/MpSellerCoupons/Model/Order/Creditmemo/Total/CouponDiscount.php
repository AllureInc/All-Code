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
namespace Webkul\MpSellerCoupons\Model\Order\Creditmemo\Total;

class CouponDiscount extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Collect credit memo subtotal
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $orderCoupondiscountTotal = $order->getCoupondiscountTotal();
        if ($orderCoupondiscountTotal) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderCoupondiscountTotal);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderCoupondiscountTotal);
        }
        return $this;
    }
}
