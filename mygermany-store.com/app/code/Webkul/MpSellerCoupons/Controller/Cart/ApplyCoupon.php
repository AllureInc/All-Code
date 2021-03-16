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
namespace Webkul\MpSellerCoupons\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

/**
 * Webkul MpSellerCoupons cart ApplyCoupon Controller.
 */
class ApplyCoupon extends Action
{
    /**
     * @var int
     */
    protected $_resultPageFactory;

    /**
     * @var PageFactory
     */
    protected $sellerId;

    /**
     * @var \Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpDataHelper;

    /**
     * @param Context                                                        $context
     * @param PageFactory                                                    $resultPageFactory
     * @param \Webkul\MpSellerCoupons\Helper\Data                            $dataHelper
     * @param \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     * @param \Magento\Checkout\Model\Cart                                   $cartModel
     * @param \Webkul\Marketplace\Helper\Data                                $mpDataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpSellerCoupons\Helper\Data $dataHelper,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\Session\SessionManagerFactory $sessionManagerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Marketplace\Helper\Data $mpDataHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $sessionModel
    ) {
        $this->_messageManager = $context->getMessageManager();
        $this->sessionManagerFactory = $sessionManagerFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        $this->moduleManager = $moduleManager;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        $this->_cartModel = $cartModel;
        $this->_mpDataHelper = $mpDataHelper;
        $this->_storeManager = $storeManager;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_sessionModel = $sessionModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $sellerInfo = [];
        $couponFlag = '';
        // $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result = $this->_resultJsonFactory->create();
        if ($this->getRequest()->isPost()) {
            $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
            $wholeFields = $this->getRequest()->getParams();
            $quote = $this->_cartModel->getQuote();

            $sellerInfo = $this->_dataHelper->getSellerInfoByQuote($quote);

            $couponFlag = $this->isCouponExist($wholeFields);
        
            if ($couponFlag['coupon_status']==true) {
                $this->sellerId = $wholeFields['seller_id'];
                $sellerTotalAmount = $sellerInfo[$wholeFields['seller_id']]['total'];

                $couponInfo = $this->_dataHelper->getAppliedCoupons();

                foreach ($couponFlag['coupon_object'] as $index => $coupon) {
                    if ($coupon->getStatus() == "active") {
                        $expireDate = $this->_dataHelper
                                    ->getDateTimeAccordingTimeZone($coupon->getExpireAt());
                        $currentDate = $this->_dataHelper->getCurrentDate();

                        if (strtotime($currentDate) <= strtotime($expireDate)) {
                            $amount = $this->_getCouponAmount($coupon->getCouponValue(), $sellerTotalAmount);
                            $couponInfo[$wholeFields['seller_id']] = [
                                            'entity_id'=>$coupon->getEntityId(),
                                            'seller_id'=>$wholeFields['seller_id'],
                                            'coupon_code'=>$wholeFields['coupon_code'],
                                            'amount'=>-$amount,
                                            'notified' => 0,
                                            "expire_at"=>$coupon->getExpireAt(),
                                            'currency_code' => $currencyCode
                                        ];
                            $this->_dataHelper->setCouponCheckoutSession($couponInfo);
                            $data = [
                                'status' => 1,
                                'message' => __("Coupon code has been applied successfully")
                            ];
                            $this->_dataHelper->refreshPricesAtCartPage();
                        } else {
                            $data = [
                                'status' => 0,
                                'message' => __("Coupon code has been expired")
                            ];
                        }
                    } else {
                        $data = [
                            'status' => 0,
                            'message' => __("Invalid coupon Code.")
                        ];
                    }
                }
            } else {
                    $data = [
                        'status' => 0,
                        'message' => __("Invalid coupon Code.")
                    ];
            }
        }
        $result->setData($data);
        return $result;
    }

    /**
     * get seller coupon amount
     * @param  int $couponAmount
     * @param  int $sellerTotalAmount
     * @return integer
     */
    private function _getCouponAmount($couponAmount, $sellerTotalAmount)
    {
        if ($couponAmount > $sellerTotalAmount) {
            $amount = $sellerTotalAmount;
        } else {
            $amount = $couponAmount;
        }
        $rate = $this->getCurrentCurrencyRate();
        if ($rate) {
            $amount = $amount * $rate ;
        }
        if ($this->moduleManager->isOutputEnabled("Webkul_MpRewardSystem")) {
            $amount = $this->checkSellerRewardsApplied($amount);
        }
        return $amount;
    }

    /**
     * checke if seller credits mod is installed
     *
     * @param [type] $amount
     * @return void
     */
    public function checkSellerRewardsApplied($amount)
    {
        $sellerId = $this->sellerId;
        $rewardInfo = $this->sessionManagerFactory->create()->getRewardInfo();
        if (isset($rewardInfo[$sellerId])) {
            $amount -= $rewardInfo[$sellerId]['used_reward_points'];
        }
        return $amount;
    }

    /**
     * check coupon code status
     * @param  array $inputParams contain inputed data
     * @return array
     */
    private function isCouponExist($inputParams)
    {
        $flag = false;
        $couponData = [];
        $couponCollection = $this->_mpSellerCouponsRepository
                ->getCouponBySellerCouponCode(
                    $inputParams['seller_id'],
                    $inputParams['coupon_code']
                );
        if ($couponCollection->getSize()) {
            $flag = true;
            $couponData ['coupon_object'] = $couponCollection;
        }
        $couponData ['coupon_status'] = $flag;
        return $couponData;
    }

    /**
     * Return rate of current currency according to the base currency
     * @return int
     */
    public function getCurrentCurrencyRate()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyRate();
    }
}
