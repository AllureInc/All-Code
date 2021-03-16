<?php

namespace Mangoit\Productfaq\Controller\Adminhtml\Productfaq;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Ced\Productfaq\Controller\Adminhtml\Productfaq\Index
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    { 
        $sellerCollection = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq')->getCollection()
        ->addFieldToFilter('admin_notification', ['neq' => 0]);
        if ($sellerCollection->getSize()) {
            $this->_updateNotification($sellerCollection);
        }
       //die("kjh"); 
        /**
 * @var \Magento\Backend\Model\View\Result\Page $resultPage 
*/
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Productfaq::management');
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(__('Product Faq Management'), __('Product Faq Management'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Faq Management'));

        return $resultPage;
    }
    
    protected function _updateNotification($collection)
    {
        foreach ($collection as $value) {
            $value->setAdminNotification(0);
            $value->setId($value->getId())->save();
        }
    }
}
