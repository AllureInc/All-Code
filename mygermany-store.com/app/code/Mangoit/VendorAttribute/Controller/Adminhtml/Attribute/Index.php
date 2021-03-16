<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorAttribute
 * @author    Mangoit
 */
namespace Mangoit\VendorAttribute\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Index extends Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    // protected function _isAllowed()
    // {
    //     return $this->_authorization->isAllowed('Mangoit_VendorAttribute::vendorattribute');
    // }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Vendor's Attribute"));
        return $resultPage;
    }
}
