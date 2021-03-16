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
namespace Webkul\MpSellerCoupons\Model;

use Webkul\MpSellerCoupons\Api\Data\MpSellerCouponsInterface;
use Magento\Framework\DataObject\IdentityInterface;

class MpSellerCoupons extends \Magento\Framework\Model\AbstractModel implements
    MpSellerCouponsInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * MpSellerCoupons's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    /**#@-*/

    /**
     * MpSellerCoupons cache tag
     */
    const CACHE_TAG = 'marketplace_mpsellercoupons';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_mpsellercoupons';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_mpsellercoupons';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons');
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Product
     *
     * @return \Webkul\MpSellerCoupons\Model\MpSellerCoupons
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enable'), self::STATUS_DISABLED => __('Disable')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * get seller id
     * @return int
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * get shop name
     * @return string
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * get latitude
     * @return string
     */
    public function getCouponCode()
    {
        return parent::getData(self::COUPON_CODE);
    }

    /**
     * get longitude
     * @return string
     */
    public function getCouponValue()
    {
        return parent::getData(self::COUPON_VALUE);
    }

    /**
     * get Country
     * @return string
     */
    public function getUsedDescription()
    {
        return parent::getData(self::USED_DESCRIPTION);
    }

    /**
     * get status of coupon
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * get created time
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * get used date of coupon
     * @return timestamp
     */
    public function getUsedAt()
    {
        return parent::getData(self::USED_AT);
    }

    /**
     * get expire date of coupon
     * @return timestamp
     */
    public function getExpireAt()
    {
        return parent::getData(self::EXPIRE_AT);
    }

    /**
     * set seller id
     * @param int $sellerId seller id
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * set order id
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ENTITY_ID, $orderId);
    }

    /**
     * set coupon code
     * @param string $couponCode
     */
    public function setCouponCode($couponCode)
    {
        return $this->setData(self::COUPON_CODE, $couponCode);
    }

    /**
     * set coupon value
     * @param string $couponValue=
     */
    public function setCouponValue($couponValue)
    {
        return $this->setData(self::COUPON_VALUE, $couponValue);
    }

    /**
     * set used description ov coupon
     * @param string $usedDescription
     */
    public function setUsedDescription($usedDescription)
    {
        return $this->setData(self::USED_DESCRIPTION, $usedDescription);
    }

    /**
     * set status
     * @param int $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * set created at of coupon
     * @param  timestamp $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * set used date of coupon
     * @param timestamp $usedAt
     */
    public function setUsedAt($usedAt)
    {
        return $this->setData(self::USED_AT, $usedAt);
    }

    /**
     * set expire date of coupon
     * @param timestamp $expireAt
     */
    public function setExpireAt($expireAt)
    {
        return $this->setData(self::EXPIRE_AT, $expireAt);
    }
}
