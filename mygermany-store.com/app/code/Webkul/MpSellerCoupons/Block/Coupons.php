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
namespace Webkul\MpSellerCoupons\Block;

use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\Seller;

/**
 * Webkul MpSellerCoupons Coupons Block
 */
class Coupons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_urlinterface;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * @var \Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpDataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context               $context
     * @param \Magento\Customer\Model\Session                                $customerSession
     * @param \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     * @param \Magento\Directory\Model\Currency                              $currency
     * @param \Webkul\MpSellerCoupons\Helper\Data                            $dataHelper
     * @param \Magento\Checkout\Model\Cart                                   $cartModel
     * @param \Webkul\Marketplace\Helper\Data                                $mpDataHelper
     * @param \Magento\Checkout\Model\Session                                $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface                      $objectManager
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\MpSellerCoupons\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Cart $cartModel,
        \Webkul\Marketplace\Helper\Data $mpDataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_urlinterface = $context->getUrlBuilder();
        $this->_customerSession = $customerSession;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        $this->_currency = $currency;
        $this->_dataHelper = $dataHelper;
        $this->moduleManager = $moduleManager;
        $this->_cartModel = $cartModel;
        $this->_mpDataHelper = $mpDataHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
        $this->setCollection($this->getCoupons());
    }

    /**
     * get parameters
     * @return array
     */
    public function getParams()
    {
        $filters = $this->getRequest()->getParams();
        return $filters;
    }

    /**
     * get seller coupons
     * @return object
     */
    public function getCoupons()
    {
        $filters = $this->getParams();
        $sellerId = $this->_customerSession
                    ->getCustomer()->getId();
        $couponsCollection = $this->_mpSellerCouponsRepository
                            ->getCouponsBySellerId($sellerId);
        if (isset($filters['code']) && $filters['code']!='') {
            $filters['code'] = strip_tags($filters['code']);
            $couponsCollection->addFieldToFilter(
                'coupon_code',
                [
                    'eq'=>trim($filters['code'])
                ]
            );
        }
        $coupons = [];
        if (!empty($filters['couponstatus'])) {
            if ($filters['couponstatus'] == "expired") {
                $couponsList = $this->_mpSellerCouponsRepository
                            ->getCouponsBySellerId($sellerId);
                foreach ($couponsList as $coupon) {
                    $expireDate = $this->_dataHelper
                                ->getDateTimeAccordingTimeZone(
                                    $coupon->getExpireAt()
                                );
                    $currentDate = $this->_dataHelper->getCurrentDate();

                    if (strtotime($currentDate) > strtotime($expireDate)) {
                        $coupons[] = $coupon->getEntityId();
                    }
                }
                $couponsCollection->addFieldToFilter(
                    'entity_id',
                    [
                        'in'=>$coupons
                    ]
                );
                $couponsCollection->addFieldToFilter(
                    'status',
                    [
                        'eq'=>"active"
                    ]
                );
            } elseif ($filters['couponstatus'] == "active") {
                $couponsList = $this->_mpSellerCouponsRepository
                            ->getCouponsBySellerId($sellerId);
                foreach ($couponsList as $coupon) {
                    $expireDate = $this->_dataHelper
                                ->getDateTimeAccordingTimeZone(
                                    $coupon->getExpireAt()
                                );
                    $currentDate = $this->_dataHelper->getCurrentDate();
                    if (strtotime($currentDate) < strtotime($expireDate)) {
                        $coupons[] = $coupon->getEntityId();
                    }
                }
                $couponsCollection->addFieldToFilter(
                    'entity_id',
                    [
                        'in'=>$coupons
                    ]
                );
                $couponsCollection->addFieldToFilter(
                    'status',
                    [
                        'eq'=>"active"
                    ]
                );
            } else {
                $couponsCollection->addFieldToFilter(
                    'status',
                    [
                        'eq'=>"used"
                    ]
                );
            }
        }
        $couponsCollection->setOrder('entity_id', 'DESC');
        return $couponsCollection;
    }

    /**
     * Get currency symbol for current locale and currency code
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * get current url
     * @return string
     */
    public function getCurrentSiteUrl()
    {
        return $this->_urlinterface->getCurrentUrl();
    }
    
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCoupons()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mp-sellercoupon.coupon.list.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get seller seller data
     * @return array
     */
    public function getSellersDetail()
    {
        $sellers = [];
        $sellersInfo = [];
        $shopName = '';
        $customerId  = $this->_customerSession->getCustomerId();
        $quote = $this->_cartModel->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $mpAssignEntityId = '';
            $options = json_decode($item->getOptionByCode('info_buyRequest')->getValue(), true);
            if (isset($options['mpassignproduct_id'])) {
                $mpAssignEntityId = $options['mpassignproduct_id'];
            }
            if ($mpAssignEntityId) {
                $mpAssignModel = $this->_objectManager->create(
                    'Webkul\MpAssignProduct\Model\Items'
                )->load($mpAssignEntityId);
                $sellers[] = $mpAssignModel->getSellerId();
            } else {
                $mpProductCollection = $this->_mpDataHelper
                                ->getSellerProductDataByProductId($item->getProductId());
                foreach ($mpProductCollection as $mpProduct) {
                    $sellers[] = $mpProduct->getSellerId();
                }
            }
        }

        $mpSellersCollection = $this->_mpSellerCouponsRepository
                                ->getSellersBySellerIds($sellers);
                                
        foreach ($mpSellersCollection as $seller) {
            if ($seller->getShopTitle()!=null) {
                $shopName = $seller->getShopTitle();
            } else {
                $shopName = $seller->getShopUrl();
            }
            $sellersInfo[$seller->getSellerId()] = [
                'shop_name'=>$shopName,
                'shop_url'=>$seller->getShopUrl()
            ];
        }
        return $sellersInfo;
    }

    /**
     * get applied coupons array from checkout session
     * @return array
     */
    public function getCouponsFromSession()
    {
        $appliedCoupons = $this->_dataHelper
                                ->getAppliedCoupons();
        return $appliedCoupons;
    }

    /**
     * get Coupon status
     * @param  timestamp $createDate
     * @param  timestamp $expireDate
     * @return string|null
     */
    public function checkCouponStatus($createDate, $expireDate)
    {
        $couponStatus = '';
        if (strtotime($createDate) > strtotime($expireDate)) {
            $couponStatus = 'expired';
        }
        if (strtotime(date('Y-m-d h:i:s')) > strtotime($expireDate)) {
            $couponStatus = 'expired';
        }
        return $couponStatus;
    }

    public function updateCouponNotificationInSession($sellerId)
    {
        $appliedCoupons = $this->_dataHelper
                                ->getAppliedCoupons();
        $appliedCoupons[$sellerId]['notified'] = 1;
        $this->_dataHelper->setCouponCheckoutSession($appliedCoupons);
    }

    public function getCouponRemoveNotificationFromSession()
    {
        return $this->_dataHelper->getRemoveCouponNotificationInSession();
    }

    public function setCouponRemoveNotificationFromSession($status)
    {
        $this->_dataHelper->setRemoveCouponNotificationInSession($status);
    }

    /**
     * checks if Reward System Module is active
     *
     * @return boolean
     */
    public function isRewardSystemEnabled()
    {
        return $this->moduleManager->isOutputEnabled("Webkul_MpRewardSystem");
    }

    /**
     * checks if Gift Card Module is active
     *
     * @return boolean
     */
    public function isGiftCardEnabled()
    {
        return $this->moduleManager->isOutputEnabled("Webkul_GiftCard");
    }
}
