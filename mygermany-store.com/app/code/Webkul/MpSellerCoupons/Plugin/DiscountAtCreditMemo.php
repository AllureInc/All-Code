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
namespace Webkul\MpSellerCoupons\Plugin;

class DiscountAtCreditMemo extends \Magento\Sales\Block\Order\Totals
{
    public function afterGetTotals(
        \Magento\Sales\Block\Order\Totals $subject,
        $result
    ) {
        $url = $subject->_storeManager->getStore()->getCurrentUrl();
        $adminSalesOrderView = strpos($url, "sales/order/view");
        $adminSalesOrderInvoiceNew = strpos($url, "admin/sales/order_invoice/new");
        $adminSalesOrderInvoiceView = strpos($url, "admin/sales/order_invoice/view");
        if ((array_key_exists("discount", $result)) ||
            ( $adminSalesOrderView > 0) ||
            ($adminSalesOrderInvoiceNew < 0) ||
            ($adminSalesOrderInvoiceView > 0)
        ) {
            return $result;
        }
        foreach ($result as $totalItem) {
            if ($totalItem['code'] == 'subtotal') {
                $orderProduct = $this->checkSellerCouponApplied($subject);
                if ($orderProduct->getSize()) {
                    $discount = 0;
                    $couponCode = "";
                    foreach ($orderProduct as $product) {
                        $discount = $discount + $product->getCouponValue();
                        $couponCode = $couponCode.' '.$product->getCouponCode();
                    }
                    $obj = new \Magento\Framework\DataObject;
                    $data = [
                        'code' => 'discount',
                        'value' => -$discount,
                        'base_value' => -$discount,
                        'label' => __('Discount').' ('.$couponCode.')'
                    ];
                    $obj->setData($data);
                    array_push($result, $obj);
                }
            }
        }
        return $result;
    }

    public function checkSellerCouponApplied($subject)
    {
        $order = $subject->getOrder();
        $orderId = $order->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Webkul\MpSellerCoupons\Model\MpSellerCoupons');
        $model = $model->getCollection()
                    ->addFieldToFilter('order_id', $orderId);
        return $model;
    }
}
