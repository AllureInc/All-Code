<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Productfaq\Controller\Adminhtml\Productfaq;

class Delete extends \Magento\Backend\App\Action
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
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                
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
