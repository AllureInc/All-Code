<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorAttribute
 * @author    Mangoit
 */
namespace Mangoit\FskRestricted\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Categoryform extends Action
{
    /**
     * @param Action\Context $context
     */
    protected $newObjectManager;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->newObjectManager = $objectmanager;
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
        $resultPage->getConfig()->getTitle()->prepend(__("Add Countries "));
        return $resultPage;
        // $param = $this->getRequest()->getParams();
        // unset($param['key']);
        // unset($param['form_key']);
        // unset($param['selectAll']);
        // echo "<pre>";
        // print_r($param);
        // die();
    }

    public function getAllParameters()
    {
         $param = $this->getRequest()->getParams();
        unset($param['key']);
        unset($param['form_key']);
        unset($param['selectAll']);
        return $param;
    }

}