<?php
/**
 * @category   Webkul
 * @package    Webkul_MpSellerCoupons
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpSellerCoupons CancelCoupon Controller.
 */
class CancelCoupon extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param Context                             $context
     * @param PageFactory                         $resultPageFactory
     * @param \Webkul\MpSellerCoupons\Helper\Data $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\MpSellerCoupons\Helper\Data $dataHelper
    ) {
        $this->_messageManager = $context->getMessageManager();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $result = $this->_resultJsonFactory->create();
                $inputedData = $this->getRequest()->getparams();
                $couponInfo = $this->_dataHelper->getAppliedCoupons();
                foreach ($couponInfo as $key => $coupon) {
                    if ($coupon['seller_id'] == $inputedData['remove']) {
                        unset($couponInfo[$key]);
                        $this->_dataHelper->setRemoveCouponNotificationInSession(true);
                    }
                }
                $this->_dataHelper->setCouponCheckoutSession($couponInfo);
                $this->_dataHelper->refreshPricesAtCartPage();
                $returnData = [
                    'status' => 1,
                    'message' => __('Coupon removed successfully')
                ];
            } else {
                $returnData = [
                    'status' => 0,
                    'message' => __('Something Went Wrong')
                ];
            }
            return $result->setData($returnData);
        } catch (\Exception $e) {
            $returnData = [
                'status' => 0,
                'message' => __('Something Went Wrong')
            ];
            return $result->setData($returnData);
        }
    }
}
