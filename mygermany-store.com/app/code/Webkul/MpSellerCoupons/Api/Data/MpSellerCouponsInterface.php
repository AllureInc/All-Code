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
namespace Webkul\MpSellerCoupons\Api\Data;

/**
 * MpSellerCoupons interface.
 * @api
 */
interface MpSellerCouponsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID         = 'entity_id';
    const ORDER_ID          = 'order_id';
    const SELLER_ID         = 'seller_id';
    const COUPON_CODE       = 'coupon_code';
    const COUPON_VALUE      = 'coupon_value';
    const USED_DESCRIPTION  = 'used_description';
    const STATUS            = 'status';
    const CREATED_AT        = 'created_at';
    const USED_AT           = 'used_at';
    const EXPIRE_AT         = 'expire_at';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * get seller id
     * @return int
     */
    public function getSellerId();

    /**
     * get shop name
     * @return string
     */
    public function getOrderId();

    /**
     * get latitude
     * @return string
     */
    public function getCouponCode();

    /**
     * get longitude
     * @return string
     */
    public function getCouponValue();

    /**
     * get Country
     * @return string
     */
    public function getUsedDescription();

    /**
     * get status of coupon
     * @return int
     */
    public function getStatus();

    /**
     * get created time
     * @return timestamp
     */
    public function getCreatedAt();

    /**
     * get used date of coupon
     * @return timestamp
     */
    public function getUsedAt();

    /**
     * get expire date of coupon
     * @return timestamp
     */
    public function getExpireAt();

    /**
     * set seller id
     * @param int $sellerId seller id
     */
    public function setSellerId($sellerId);

    /**
     * set order id
     * @param int $orderId
     */
    public function setOrderId($orderId);

    /**
     * set coupon code
     * @param string $couponCode
     */
    public function setCouponCode($couponCode);

    /**
     * set coupon value
     * @param string $couponValue=
     */
    public function setCouponValue($couponValue);

    /**
     * set used description ov coupon
     * @param string $usedDescription
     */
    public function setUsedDescription($usedDescription);

    /**
     * set status
     * @param int $status
     */
    public function setStatus($status);

    /**
     * set created at of coupon
     * @param  timestamp $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * set used date of coupon
     * @param timestamp $usedAt
     */
    public function setUsedAt($usedAt);

    /**
     * set expire date of coupon
     * @param timestamp $expireAt
     */
    public function setExpireAt($expireAt);
}
