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

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Webkul MpSellerCoupons GenerateCoupon Controller.
 */
class GenerateCoupon extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Context                                     $context
     * @param FormKeyValidator                            $formKeyValidator
     * @param SaveCoupon                                  $saveCoupon
     * @param PageFactory                                 $resultPageFactory
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        SaveCoupon $saveCoupon,
        PageFactory $resultPageFactory
    ) {
        $this->_saveCoupon = $saveCoupon;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($this->getRequest()->isPost()) {
            $expDate = $this->getRequest()->getParam('coupon_expiry');
            $expDate = Date($expDate.' 23:59');
            $currDate = $this->_saveCoupon->getDateTimeAccordingTimeZone();
            if (strtotime($expDate) < strtotime($currDate)) {
                $this->messageManager->addError(
                    __('Expire Date should have a valid value')
                );
                return $resultRedirect->setPath('mpsellercoupons/index/index');
            }
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/index/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $wholeFields = $this->getRequest()->getParams();
            $wholeFields['coupon_prefix'] = strip_tags($wholeFields['coupon_prefix']);
            $this->_saveCoupon->save($wholeFields);
            $this->messageManager->addSuccess(
                __('Your Coupons was successfully generated')
            );
        }
        return $resultRedirect->setPath('mpsellercoupons/index/index');
    }
}
