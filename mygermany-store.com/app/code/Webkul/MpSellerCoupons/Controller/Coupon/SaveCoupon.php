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
namespace Webkul\MpSellerCoupons\Controller\Coupon;

use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface;

/**
 * Webkul MpSellerCoupons SaveCoupon class.
 */
class SaveCoupon
{
    const CHARS_LOWERS                          = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_UPPERS                          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_DIGITS                          = '0123456789';
    const CHARS_COUPON_LOWERS                   = 'abcdefghjkmnpqrstuvwxyz';
    const CHARS_COUPON_UPPERS                   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const CHARS_COUPON_DIGITS                   = '23456789';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currencyModel;

    /**
     * @var Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_helperData;

    /**
     * @var Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponRepository;

    /**
     * @var \Webkul\MpSellerCoupons\Model\MpSellerCoupons
     */
    protected $_mpSellerCouponsModel;
 
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Directory\Model\Currency                    $currencyModel
     * @param \Webkul\MpSellerCoupons\Helper\Data                  $helperData
     * @param MpSellerCouponsRepositoryInterface                   $mpSellerCouponRepository
     * @param \Webkul\MpSellerCoupons\Model\MpSellerCoupons        $mpSellerCouponsModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currencyModel,
        \Webkul\MpSellerCoupons\Helper\Data $helperData,
        MpSellerCouponsRepositoryInterface $mpSellerCouponRepository,
        \Webkul\MpSellerCoupons\Model\MpSellerCoupons $mpSellerCouponsModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_currencyModel = $currencyModel;
        $this->_helperData = $helperData;
        $this->_mpSellerCouponRepository = $mpSellerCouponRepository;
        $this->_mpSellerCouponsModel = $mpSellerCouponsModel;
        $this->_timezoneInterface = $timezoneInterface;
    }

    public function save($wholeFields)
    {
        $count = 0;
        $sellerId = $this->_customerSession
                    ->getCustomer()->getId();
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $baseCurrency = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $allowedCurrency = $this->_currencyModel->getConfigAllowCurrencies();
        
        $currencyRate = $this->_currencyModel
                    ->getCurrencyRates(
                        $baseCurrency,
                        $allowedCurrency
                    );
        $price = $wholeFields['coupon_value'];
        $model = $this->_mpSellerCouponsModel;

        while ($count < $wholeFields['coupon_quantity']) {
            $code = $wholeFields['coupon_prefix'].$this->generateCoupon();
            $collection = $this->_mpSellerCouponRepository->getCouponByCouponCode($code);
            if (!$collection->getSize()) {
                $expireAt =  $wholeFields['coupon_expiry'].'23:59:59';
                $fields = [
                    'coupon_code'   =>$code,
                    'seller_id'     =>$sellerId,
                    'coupon_value'  =>$price,
                    'status'        =>'active',
                    'created_at'    =>$this->getDateTimeAccordingTimeZone(),
                    'expire_at'     =>$expireAt
                ];
                $model->setData($fields);
                $model->save();
                $modelColl = $this->_mpSellerCouponsModel->getCollection();
                $count++;
            }
        }
    }

    /**
     * getnerate coupon string
     * @param  integer $length
     * @return string
     */
    public function generateCoupon($length = 10)
    {
        $chars = self::CHARS_COUPON_LOWERS
            . self::CHARS_COUPON_UPPERS
            . self::CHARS_COUPON_DIGITS;
         return   $this->getRandomString($length, $chars);
    }

    /**
     * create string
     * @param  integer $len
     * @param  string $chars
     * @return string
     */
    protected function getRandomString($len, $chars = null)
    {
        if ($chars === null) {
            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
        }
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }


    /**
     * get converted time according to locale
     * @param  timestmap $dateTime
     * @return timestamp
     */
    public function getDateTimeAccordingTimeZone()
    {
        $dateTimeAsTimeZone = $this->_timezoneInterface->date(new \DateTime(date("Y/m/d H:i:s")))
                                                       ->format('Y/m/d H:i:s');
        return $dateTimeAsTimeZone;
    }
}
