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
namespace Webkul\MpSellerCoupons\Model\Quote\Address\Total;

class CouponDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface              $priceCurrency
     * @param \Webkul\MpSellerCoupons\Helper\Data                            $dataHelper
     * @param \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Webkul\MpSellerCoupons\Helper\Data $dataHelper,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_dataHelper = $dataHelper;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $discountCouponAmount = 0;
        $couponInfo = $this->_dataHelper->getAppliedCoupons();

        $sellerData = $this->_dataHelper->getSellerInfoByQuote($quote);
        if ($couponInfo) {
            foreach ($couponInfo as $index => $info) {
                if (array_key_exists($info['seller_id'], $sellerData)) {
                    $couponModel = $this->_mpSellerCouponsRepository
                            ->getCouponByEntityId($info['entity_id']);

                    if ($couponModel->getStatus() != "used") {
                        $expireDate = $this->_dataHelper->getDateTimeAccordingTimeZone($info['expire_at']);
                        $currentDate = $this->_dataHelper->getCurrentDate();
                        if (strtotime($currentDate) <= strtotime($expireDate)) {
                            $discountCouponAmount = $discountCouponAmount + $info['amount'];
                        } else {
                            unset($couponInfo[$index]);
                        }
                    }
                    $flag = 1;
                }
            }

            $total->addTotalAmount('coupondiscount_total', $discountCouponAmount);
            $quote->setCoupondiscountTotal($discountCouponAmount);
            $quote->getShippingAddress()->setCoupondiscountTotal($discountCouponAmount);
        } else {
            $total->addTotalAmount('coupondiscount_total', $discountCouponAmount);
            $quote->setCoupondiscountTotal($discountCouponAmount);
            $quote->getShippingAddress()->setCoupondiscountTotal($discountCouponAmount);
        }
        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $amount = $quote->getShippingAddress()->getCoupondiscountTotal();
        return [
            'code' => 'coupondiscount_total',
            'title' => $this->getLabel(),
            'value' => $amount
        ];
    }

    /**
     * get label
     * @return string
     */
    public function getLabel()
    {
        return __('Coupon Discount');
    }
}
