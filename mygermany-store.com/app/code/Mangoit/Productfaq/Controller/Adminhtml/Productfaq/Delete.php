<?php
/**
 * Mango IT Solutions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 */
namespace Mangoit\Productfaq\Controller\Adminhtml\Productfaq;

class Delete extends \Ced\Productfaq\Controller\Adminhtml\Productfaq\Delete
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
        */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                $_misProductFaq = $this->_objectManager->create('Mangoit\Productfaq\Model\Misproductfaq');
                $_customerRepositoryInterface = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
                $model->load($id);
                //check either customer translated any FAQ or not - Start
                $sellerCustomerRecord = $_customerRepositoryInterface->getById($model->getVendorId());
                $isTranslated = 0;
                if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
                    $isTranslated = $translationObj->getValue();
                }
                //check either customer translated any FAQ or not - End
                if (!$isTranslated) {
                    $idToLoad = ($model->getParentFaqId() == 0) ? $id: $model->getParentFaqId();
                    $misFaqObj = $_misProductFaq->load($idToLoad,'default_faq_id'); 
                    $unsFaqIds = unserialize($misFaqObj->getStorewiseFaqIds());

                    $modelNewObj = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                    $faqsToDelete = $modelNewObj->getCollection()->addFieldToFilter('id',['in' => $unsFaqIds]);
                    foreach ($faqsToDelete as $faqDelVlue) {
                        $faqDelVlue->delete();
                    }
                    $misFaqObj->delete();

                } else {
                    $title = $model->getTitle();
                    $model->delete();  
                }
                $this->messageManager->addSuccess(__('The FAQ has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
               
                $this->messageManager->addError($e->getMessage());
                
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
       
        $this->messageManager->addError(__('We can\'t find a FAQ to delete.'));
       
        return $resultRedirect->setPath('*/*/');
    }
}
