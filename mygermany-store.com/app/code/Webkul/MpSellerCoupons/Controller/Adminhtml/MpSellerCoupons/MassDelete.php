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
namespace Webkul\MpSellerCoupons\Controller\Adminhtml\MpSellerCoupons;

use Magento\Backend\App\Action;
use Webkul\MpSellerCoupons\Model\MpSellerCoupons;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Webkul\MpSellerCoupons\Model\MpSellerCoupons
     */
    protected $_mpSellerCoupons;

    /**
     * filter object of Filter
     * @var Filter
     */
    protected $_filter;

    /**
     * @param Action\Context  $context
     * @param Filter          $filter
     * @param MpSellerCoupons $mpSellerCoupons
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        MpSellerCoupons $mpSellerCoupons
    ) {
        $this->_filter = $filter;
        $this->_mpSellerCoupons = $mpSellerCoupons;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpSellerCoupons::index');
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $mpSellerCouponModel = $this->_mpSellerCoupons;
        $model = $this->_filter;
        $collection = $model->getCollection($mpSellerCouponModel->getCollection());
        foreach ($collection as $coupon) {
            $coupon->delete()->save();
        }
        $this->messageManager->addSuccess(__('seller(s) coupon deleted successfully.'));
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
