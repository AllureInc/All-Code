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
use Webkul\MpSellerCoupons\Helper\Data;

/**
 * Webkul MpSellerCoupons SalesOrderPlaceAfterObserver Observer.
 */
class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpDataHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Webkul\Marketplace\Model\Orders
     */
    protected $_mpOrder;

    /**
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $_mpSalesList;

    /**
     * @var \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * @param Data                                                           $helperData
     * @param \Webkul\Marketplace\Helper\Data                                $mpDataHelper
     * @param \Magento\Sales\Model\Order                                     $order
     * @param \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     * @param \Webkul\Marketplace\Model\Orders                               $mpOrder
     * @param \Webkul\Marketplace\Model\Saleslist                            $mpSalesList
     */
    public function __construct(
        Data $helperData,
        \Webkul\Marketplace\Helper\Data $mpDataHelper,
        \Magento\Sales\Model\Order $order,
        \Psr\Log\LoggerInterface $log,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        \Webkul\Marketplace\Model\Orders $mpOrder,
        \Webkul\Marketplace\Model\Saleslist $mpSalesList
    ) {
        $this->_helperData = $helperData;
        $this->log = $log;
        $this->_mpDataHelper = $mpDataHelper;
        $this->_order = $order;
        $this->_orderItem = $orderItem;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        $this->_mpOrder = $mpOrder;
        $this->_mpSalesList = $mpSalesList;
    }

    /**
     * mp_order_save_after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $couponInfo = [];
        $appliedCouponAmount = 0;
        $lastOrderId = $observer->getOrder()->getId();
        $order = $this->_order->load($lastOrderId);
        $totalCouponDiscount = 0;
        $sellerData = $this->_helperData->getSellerInfoByQuote($order, $isOrder = true);
        $couponInfo = $this->_helperData->getAppliedCoupons();
        if (!empty($couponInfo)) {
            foreach ($couponInfo as $index => $info) {
                if (isset($sellerData[$info['seller_id']])) {
                    $couponModel = $this->_mpSellerCouponsRepository
                            ->getCouponByEntityId($info['entity_id']);
                    $usedDes = "Used by customer ".
                            $order->getCustomerFirstname()." ".
                            $order->getCustomerLastname()." in order #".
                            $order->getIncrementId();

                    $couponData = [
                                'order_id'        =>$lastOrderId,
                                'used_description'=>$usedDes,
                                'used_at'         =>$this->_helperData->getCurrentDateAndTime(),
                                'status'          =>'used'
                    ];

                    $couponModel->addData($couponData)->setEntityId($info['entity_id'])->save();

                    $mpOrders = $this->_mpOrder->getCollection()
                    ->addFieldToFilter('order_id', $lastOrderId)
                    ->addFieldToFilter('seller_id', $info['seller_id']);

                    foreach ($mpOrders as $tracking) {
                        $tracking->setCouponAmount(abs($info['amount']))->save();
                    }
                    $totalCouponDiscount = $totalCouponDiscount + abs($info['amount']);
                    $totalCouponAmount = $couponModel->getCouponValue();
                    $mpSalesList = $this->_mpSalesList->getCollection()
                    ->addFieldToFilter('seller_id', $info['seller_id'])
                    ->addFieldToFilter('order_id', $lastOrderId);

                    $this->_saveCouponOnSalesList($mpSalesList, $totalCouponAmount);

                    unset($couponInfo[$index]);
                }
            }
            $totalCouponDiscount = number_format($totalCouponDiscount, 4);
           
            $order->setBaseDiscountAmount($totalCouponDiscount);
            $order->setBaseGrandTotal($order->getBaseGrandTotal()+$totalCouponDiscount);
            $order->setDiscountAmount($totalCouponDiscount);
            $order->setBaseTotalDue($order->getBaseDiscountAmount());
            $totalCouponDiscount = -$totalCouponDiscount;
            // $order->setCoupondiscountTotal(-$totalCouponDiscount);
            // for grand total while creating invoice
            $order->setCoupondiscountTotal(null);
            $order->save();
            $ordered_items = $order->getAllVisibleItems();

            $this->_helperData->setCouponCheckoutSession($couponInfo);
        }
    }

    /**
     * save coupon detail on sales list table
     * @param  object $salesListObject
     * @param  int $totalCouponAmount
     */
    private function _saveCouponOnSalesList($salesListObject, $totalCouponAmount)
    {
        foreach ($salesListObject as $salesListModel) {
            if ($totalCouponAmount) {
                if ($salesListModel->getActualSellerAmount() >= $totalCouponAmount) {
                    $appliedCouponAmount = $totalCouponAmount;
                    $totalCouponAmount = 0;
                } else {
                    $salesListModel->getActualSellerAmount();
                    $appliedCouponAmount = $salesListModel->getActualSellerAmount();
                    $totalCouponAmount = str_replace("-", "", $totalCouponAmount) - str_replace("-", "", $salesListModel->getActualSellerAmount());
                }
                $salesListModel->setIsCoupon(1);
                $salesListModel->setAppliedCouponAmount($appliedCouponAmount);
                $salesListModel->save();
            }
        }
    }
}
