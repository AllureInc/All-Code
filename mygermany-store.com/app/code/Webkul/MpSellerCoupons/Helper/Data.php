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
namespace Webkul\MpSellerCoupons\Helper;

/**
 * MpSellerCoupons data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpDataHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date
     * @param \Magento\Checkout\Model\Cart                         $cartModel
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Webkul\Marketplace\Helper\Data                      $mpDataHelper
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Marketplace\Helper\Data $mpDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currency,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_date = $date;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_storeManager = $storeManager;
        $this->_cartModel = $cartModel;
        $this->_currency = $currency;
        $this->quoteRepository = $quoteRepository;
        $this->_cartModel = $cartModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderItem = $orderItem;
        $this->_mpDataHelper = $mpDataHelper;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * get current date
     * @return timestamp
     */
    public function getCurrentDate()
    {
        $today = $this->_date->gmtDate('m/d/y');
        return $today;
    }

    /**
     * get converted time according to locale
     * @param  timestmap $dateTime
     * @return timestamp
     */
    public function getDateTimeAccordingTimeZone($dateTime)
    {
        $dateTimeAsTimeZone = $this->_timezoneInterface
                                        ->date(new \DateTime($dateTime))
                                        ->format('m/d/y');
        return $dateTimeAsTimeZone;
    }

    /**
     * get quote date
     * @return array
     */
    public function getQuoteProductIds()
    {
        $productIds = [];
        $cartAllItems = $this->_cartModel->getQuote()->getAllItems();
        foreach ($cartAllItems as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    /**
     * get seller information from quote
     * @param  object $quote
     * @return array
     */
    public function getSellerInfoByQuote($quote, $isOrder = false)
    {
        $sellerId = '';
        $sellerInfo = [];
        $cartAllItems = $quote->getAllItems();
        foreach ($cartAllItems as $item) {
            $mpAssignEntityId = '';
            
            $mpAssignEntityId = $this->_checkMpAssignItem($item, $isOrder);
            
            if ($mpAssignEntityId) {
                $mpAssignModel = $this->_objectManager->create(
                    'Webkul\MpAssignProduct\Model\Items'
                )->load($mpAssignEntityId);
                $sellerId = $mpAssignModel->getSellerId();
            } else {
                $mpProductCollection = $this->_mpDataHelper
                                ->getSellerProductDataByProductId($item->getProductId());
                foreach ($mpProductCollection as $mpProduct) {
                    $sellerId = $mpProduct->getSellerId();
                }
            }
            
            if (!empty($sellerId)) {
                $sellerInfo[$sellerId]['details'] = [
                    'product_id'=>$item->getProductId(),
                    'price'=>$item->getPrice() * $item->getQty()
                ];
            
                if (!isset($sellerInfo[$sellerId]['total'])) {
                    $sellerInfo[$sellerId]['total'] = 0;
                }

                $sellerInfo[$sellerId]['total'] =$sellerInfo[$sellerId]['total']+
                                $item->getPrice() * $item->getQty();
            }
        }
        return $sellerInfo;
    }

    /**
     * get MpAssign id of product if exist
     * @param  object $item
     * @param  boolean $isOrder
     * @return int|string
     */
    private function _checkMpAssignItem($item, $isOrder)
    {
        $serializer = $this->_objectManager->create("Magento\Framework\Serialize\Serializer\Json");

        $mpAssignEntityId = '';
        try {
            if (!$isOrder) {
                foreach ($item->getOptions() as $option) {
                    /*$itemOptions = unserialize($option['value']);*/
                    $itemOptions = $serializer->unserialize($option['value']);
                    if (array_key_exists('mpassignproduct_id', $itemOptions)) {
                        $mpAssignEntityId = $itemOptions['mpassignproduct_id'];
                    }
                    break;
                }
            } else {
                $options = $item->getProductOptions();
                foreach ($options as $option) {
                    if (isset($option['mpassignproduct_id'])) {
                        $mpAssignEntityId = $option['mpassignproduct_id'];
                    }
                    break;
                }
            }
            return $mpAssignEntityId;
        } catch (\Exception $e) {
            return $mpAssignEntityId;
        }
    }

    /**
     * get applied coupons array from checkout session
     * @return array
     */
    public function getAppliedCoupons()
    {
        $appliedCoupons = $this->_checkoutSession
                                ->getCouponInfo();

        return $appliedCoupons;
    }

    /**
     * set coupon infomation in checkout session
     * @param array $couponInfo contain cart seller info
     */
    public function setCouponCheckoutSession($couponInfo)
    {
        $appliedCoupons = $this->_checkoutSession
                                ->setCouponInfo($couponInfo);
    }

    public function updateOrderItem($couponDiscount, $itemId)
    {
        $item = $this->_orderItem->load($itemId);
        $item->setDiscountAmount($couponDiscount);
        $item->setBaseDiscountAmount($couponDiscount);
        $item->save();
        return true;
    }

    public function refreshPricesAtCartPage()
    {
        $cartQuote = $this->_cartModel->getQuote();
        $itemsCount = $cartQuote->getItemsCount();
        if ($itemsCount) {
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->collectTotals();
            $this->quoteRepository->save($cartQuote);
        }
    }

    public function updateCouponValue($toCurrency)
    {
        $couponInfo = $this->getAppliedCoupons();
        if ($couponInfo == "") {
            return true;
        }
        $newCouponInfo = $couponInfo;
        foreach ($couponInfo as $key => $coupon) {
            if ($coupon['currency_code'] != $toCurrency) {
                $fromCurrency = $coupon['currency_code'];

                $rateToBase = $this->_currency->create()->load($fromCurrency)->getAnyRate($this->_storeManager->getStore()->getBaseCurrency()->getCode());
                $rateFromBase = $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
                $coupon['amount'] = round($coupon['amount']*$rateFromBase * $rateToBase);
                $coupon['currency_code'] = $toCurrency;
                $newCouponInfo[$key] = $coupon;
            }
        }
        $this->setCouponCheckoutSession($newCouponInfo);
        return true;
    }

    /*
     * get current date and time according to locale
     */
    public function getCurrentDateAndTime()
    {
        return $this->_timezoneInterface->date(new \DateTime(date("Y/m/d H:i:sa")))
                                                       ->format('Y/m/d H:i:s');
    }

    public function setRemoveCouponNotificationInSession($status)
    {
        $this->_checkoutSession->setCouponRemoveNotification($status);
    }

    public function getRemoveCouponNotificationInSession()
    {
        return $this->_checkoutSession->getCouponRemoveNotification();
    }
}
