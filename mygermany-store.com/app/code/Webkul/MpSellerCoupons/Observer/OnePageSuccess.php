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
class OnePageSuccess implements ObserverInterface
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
     * @var \Webkul\MpSellerCoupons\Model\MpSellerCoupons
     */
    protected $_mpSellerCoupons;

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
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Webkul\MpSellerCoupons\Model\MpSellerCoupons $mpSellerCoupons,
        \Webkul\Marketplace\Model\Orders $mpOrder,
        \Webkul\Marketplace\Model\Saleslist $mpSalesList
    ) {
        $this->_helperData = $helperData;
        $this->_logger = $logger;
        $this->_mpDataHelper = $mpDataHelper;
        $this->_order = $order;
        $this->_orderItem = $orderItem;
        $this->_mpSellerCoupons = $mpSellerCoupons;
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
        $orderIds = $observer->getOrderIds();
        foreach ($orderIds as $orderId) {
            $lastOrderId = $orderId;
            $couponsData = $this->_mpSellerCoupons->getCollection()
                                ->addFieldToFilter('order_id', $lastOrderId);
            $couponCode = "";
            foreach ($couponsData as $couponData) {
                if ($couponCode == "") {
                    $couponCode = $couponData->getCouponCode();
                } else {
                    $couponCode = $couponCode.' '.$couponData->getCouponCode();
                }
            }
            $this->_logger->addInfo($couponCode);
            
            if ($couponCode == "") {
                continue;
            } else {
                $this->updateOrder($lastOrderId, $couponCode);
            }
        }
    }

    public function updateOrder($orderId, $couponCode)
    {
        try {
            $order = $this->_order->load($orderId);
            if ($order->getDiscountDescription() != "") {
                $order->setDiscountDescription($order->getDiscountDescription().', '.$couponCode);
                $order->setCouponCode($order->getDiscountDescription().', '.$couponCode);
            } else {
                $order->setDiscountDescription($couponCode);
                $order->setCouponCode($couponCode);
            }
            $order->setCoupondiscountTotal(-$order->getCoupondiscountTotal());
            $order->save();
        } catch (\Exception $e) {
            $this->_logger->addInfo($e->getMessage());
        }
    }
}
