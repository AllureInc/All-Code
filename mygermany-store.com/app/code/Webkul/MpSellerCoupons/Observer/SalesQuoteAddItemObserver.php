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
 * Webkul MpSellerCoupons SalesQuoteAddItemObserver Observer.
 */
class SalesQuoteAddItemObserver implements ObserverInterface
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
     * @param Data                            $helperData
     * @param \Webkul\Marketplace\Helper\Data $mpDataHelper
     */
    public function __construct(
        Data $helperData,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $mpDataHelper
    ) {
        $this->_helperData = $helperData;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        $this->date = $date;
        $this->_productRepository = $productRepository;
        $this->_mpDataHelper = $mpDataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getRequest()->getParam('product');
        $mpProductCollection = $this->_mpDataHelper
                                    ->getSellerProductDataByProductId($productId);
        
        $sellerId="";
        foreach ($mpProductCollection as $sellerProduct) {
            $sellerId = $sellerProduct->getSellerId();
        }
        
        /*
        *if non zero seller id check for already applied coupons
        */
        if (!empty($sellerId)) {
            $couponInfo = $this->_helperData->getAppliedCoupons();
            if ($couponInfo && isset($couponInfo[$sellerId])) {
                $couponData = $this->_mpSellerCouponsRepository
                                    ->getCouponByEntityId($couponInfo[$sellerId]['entity_id']);

                $couponAmount = $couponData->getCouponValue();
                /*
                *check if applied coupon amount is greater then the actual coupon amount
                */
                if (abs($couponInfo[$sellerId]['amount']) < $couponAmount) {
                    //Get the sum of applied coupon amount and the current product price
                    $product = $this->_productRepository->getById($productId);
                    if ($product->getSpecialPrice()) {
                        $fromDate = strtotime(str_replace("-", "", substr($product->getSpecialFromDate(), 0, -9)));
                        $toDate = strtotime(str_replace("-", "", substr($product->getSpecialToDate(), 0, -9)));
                        $today = strtotime(str_replace("-", "", substr($this->date->gmtDate(), 0, -9)));
                        if ($toDate == "") {
                            $toDate = $today+1;
                        }
                        if (($today >= $fromDate) && ($today <= $toDate)) {
                            $price = $product->getSpecialPrice();
                        } else {
                            $price = $product->getPrice();
                        }
                    } else {
                            $price = $product->getPrice();
                    }

                    if (abs($couponInfo[$sellerId]['amount']) < $couponAmount) {
                        if (abs($couponInfo[$sellerId]['amount']) + $price > $couponAmount) {
                            $couponInfo[$sellerId]['amount'] = -$couponAmount;
                        } else {
                            $couponInfo[$sellerId]['amount'] = -(abs($couponInfo[$sellerId]['amount']) + $price);
                        }
                    }
                    $this->_helperData->setCouponCheckoutSession($couponInfo);
                }
            }
        }
        return;
    }
}
