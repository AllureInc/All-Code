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

use Webkul\MpSellerCoupons\Api\Data\MpSellerCouponsRepositoryInterface;
use Magento\Customer\Model\Customer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MpSellerCouponsRepository implements \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons
     */
    protected $_resourceModel;

    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var MpSellerCouponsFactory
     */
    protected $_mpSellerCouponsFactory;

    /**
     * @var \Webukl\Marketplace\Model\Product
     */
    protected $_mpProductModel;

    /**
     * @var \Webkul\Marketplace\Model\Seller
     */
    protected $_mpSellerModel;

    /**
     * @param MpSellerCouponsFactory                                                   $mpSellerCouponsFactory
     * @param Customer                                                                      $customerModel
     * @param \Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons\CollectionFactory $collectionFactory
     * @param \Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons                   $resourceModel
     * @param \Webkul\Marketplace\Model\Product                                             $mpProductModel
     * @param \Webkul\Marketplace\Model\Seller                                              $mpSellerModel
     */
    public function __construct(
        MpSellerCouponsFactory $mpSellerCouponsFactory,
        Customer $customerModel,
        \Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons\CollectionFactory $collectionFactory,
        \Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons $resourceModel,
        \Webkul\Marketplace\Model\Product $mpProductModel,
        \Webkul\Marketplace\Model\Seller $mpSellerModel
    ) {
        $this->_customerModel = $customerModel;
        $this->_resourceModel = $resourceModel;
        $this->_mpSellerCouponsFactory = $mpSellerCouponsFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_mpProductModel = $mpProductModel;
        $this->_mpSellerModel = $mpSellerModel;
    }

    /**
     * get collection of coupon
     * @param  string $couponCode
     * @return string
     */
    public function getCouponByCouponCode($couponCode)
    {
        $couponsCollection = $this->_mpSellerCouponsFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'coupon_code',
                                [
                                    'eq'=>$couponCode
                                ]
                            );
        return $couponsCollection;
    }

    /**
     * get collection by seller id
     * @param  integer $sellerId
     * @return object
     */
    public function getCouponsBySellerId($sellerId)
    {
        $couponsCollection = $this->_mpSellerCouponsFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                [
                                    'eq'=>$sellerId
                                ]
                            );
        return $couponsCollection;
    }

    /**
     * get Coupon by entity id
     * @param  integer $entityId
     * @return object
     */
    public function getCouponById($entityId)
    {
        $coupon = $this->_mpSellerCouponsFactory->create()->load($entityId);
        return $coupon;
    }

    /**
     * get sellers collection by product ids
     * @param  array $productIds contain product ids
     * @return object
     */
    public function getSellersbyProductId(array $productIds)
    {
        $mpProductCollection = $this->_mpProductModel
                            ->getCollection()
                            ->addFieldToFilter(
                                'mageproduct_id',
                                [
                                    'in'=>$productIds
                                ]
                            );
        return $mpProductCollection;
    }

    /**
     * get sellers collection by seller ids
     * @param  array  $sellerIds contain seller ids
     * @return object
     */
    public function getSellersBySellerIds(array $sellerIds)
    {
        $mpSellersCollection = $this->_mpSellerModel
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                [
                                    'in'=>$sellerIds
                                ]
                            );
        return $mpSellersCollection;
    }

    /**
     * get coupon by seller couponcode
     * @param  int $sellerId
     * @param  string $couponCode
     * @return object
     */
    public function getCouponBySellerCouponCode($sellerId, $couponCode)
    {
        $couponCollection = $this->_mpSellerCouponsFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                [
                                    'eq'=>$sellerId
                                ]
                            )
                            ->addFieldToFilter(
                                'coupon_code',
                                [
                                    'eq'=>$couponCode
                                ]
                            );
        return $couponCollection;
    }

    /**
     * get coupon collection by entity id
     * @param  int $entityId
     * @return object
     */
    public function getCouponByEntityId($entityId)
    {
        $couponCollection = $this->_mpSellerCouponsFactory
                            ->create()
                            ->load($entityId);
        return $couponCollection;
    }
}
