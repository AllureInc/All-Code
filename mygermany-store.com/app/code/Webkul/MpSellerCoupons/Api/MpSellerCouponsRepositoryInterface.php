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
namespace Webkul\MpSellerCoupons\Api;

/**
 * @api
 */
interface MpSellerCouponsRepositoryInterface
{
    /**
     * get collection of coupon
     * @param  string $couponCode
     * @return object
     */
    public function getCouponByCouponCode($couponCode);

    /**
     * get collection by seller id
     * @param  integer $sellerId
     * @return object
     */
    public function getCouponsBySellerId($sellerId);

    /**
     * get Coupon by entity id
     * @param  integer $entityId
     * @return object
     */
    public function getCouponById($entityId);

    /**
     * get sellers collection by product ids
     * @param  array $productIds contain product ids
     * @return object
     */
    public function getSellersbyProductId(array $productIds);

    /**
     * get sellers collection by seller ids
     * @param  array  $sellerIds contain seller ids
     * @return object
     */
    public function getSellersBySellerIds(array $sellerIds);

    /**
     * get coupon by seller couponcode
     * @param  int $sellerId
     * @param  string $couponCode
     * @return object
     */
    public function getCouponBySellerCouponCode($sellerId, $couponCode);

    /**
     * get coupon collection by entity id
     * @param  int $entityId
     * @return object
     */
    public function getCouponByEntityId($entityId);
}
