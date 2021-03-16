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
namespace Webkul\MpSellerCoupons\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul MpSellerCoupons Index Controller.
 */
class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context);
    }

    /**
     * MpSellerCoupons page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_mpHelper->getIsSeparatePanel()) {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('mpsellercoupons_index_index_layout2');
            $resultPage->getConfig()->getTitle()->set(__('Gift Vouchers'));
            return $resultPage;
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Gift Vouchers'));

        return $resultPage;
    }
}
