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
use Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface;

/**
 * MassDelete class
 */
class MassDelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * @param Context                            $context
     * @param MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     * @param \Magento\Customer\Model\Session    $customerSession
     * @param PageFactory                        $resultPageFactory
     */
    public function __construct(
        Context $context,
        MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
    
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * MassDelete
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            $entityIds = $this->getRequest()->getParam('coupon_mass_delete');
            foreach ($entityIds as $entityId) {
                $couponCollection = $this->_mpSellerCouponsRepository
                                    ->getCouponById($entityId);
                $couponCollection->delete()->save();
            }
        }
        $this->messageManager->addSuccess(
            __(
                'Coupons Deleted Successfully'
            )
        );
        return $resultRedirect->setPath('mpsellercoupons/index/index');
    }
}
