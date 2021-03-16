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
class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
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
        try {
            $invoice = $observer->getInvoice();

            if (($invoice->getGrandTotal() == $invoice->getShippingAmount()) && !count($invoice->getAllItems())) {
                return;
            }
            $orderId = $invoice->getOrderId();
            $discountData = $this->getDiscountdataOnOrder($orderId);
            if ($discountData) {
                $this->updateInvoice($invoice, $discountData);
            }
        } catch (\Exception $e) {
            $this->_logger->addInfo($e->getMessage());
        }
    }

    public function getDiscountdataOnOrder($orderId)
    {
        $couponsData = $this->_mpSellerCoupons->getCollection()
                            ->addFieldToFilter('order_id', $orderId);
        if (count($couponData->getData())) {
            foreach ($couponsData as $couponData) {
                $couponData = $couponData;
            }
            if ($couponData->getEntityId()) {
                return $couponData;
            }
        }
        return false;
    }

    public function updateInvoice($invoice, $discountData)
    {
        $invoice->setDiscountAmount(-$discountData->getCouponValue());
        $invoice->setBaseDiscountAmount(-$discountData->getCouponValue());
        $invoice->setDiscountDescription($discountData->getCouponCode());
        $invoice->save();
    }
}
