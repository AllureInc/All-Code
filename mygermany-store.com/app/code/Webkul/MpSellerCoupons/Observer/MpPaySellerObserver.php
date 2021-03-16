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
 * Webkul MpSellerCoupons MpPaySellerObserver Observer.
 */
class MpPaySellerObserver implements ObserverInterface
{
    /**
     * @var \Webkul\SellerCoupons\Model\MpSellerCoupons
     */
    protected $_mpSellerCouponsModel;

    /**
     * @param \Webkul\MpSellerCoupons\Model\MpSellerCoupons $mpSellerCouponsModel
     */
    public function __construct(
        \Webkul\MpSellerCoupons\Model\MpSellerCoupons $mpSellerCouponsModel
    ) {
        $this->_mpSellerCouponsModel = $mpSellerCouponsModel;
    }

    /**
     * mp_pay_seller event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventArray = $observer->getEvent();
        $orderId = $eventArray[0]['id'];
        $sellerId = $eventArray[0]['seller_id'];

        $couponCollection = $this->_mpSellerCouponsModel->getCollection()
                            ->addFieldToFilter(
                                'status',
                                [
                                    'eq'=>'used'
                                ]
                            )
                            ->addFieldToFilter(
                                'seller_id',
                                [
                                    'eq'=>$sellerId
                                ]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                [
                                    'eq'=>$orderId
                                ]
                            );
        if ($couponCollection->getSize()) {
            foreach ($couponCollection as $coupon) {
                $coupon->setAmountDeductStatus(1);
                $coupon->save();
            }
        }
    }
}
