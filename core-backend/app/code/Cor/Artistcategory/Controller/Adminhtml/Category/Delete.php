<?php
namespace Cor\Artistcategory\Controller\Adminhtml\Category;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
                $banner = $this->_objectManager->get('Cor\Artistcategory\Model\Artistcategory')->load($id);
                $banner->delete();
                $this->messageManager->addSuccess(
                    __('The category has been deleted successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        $this->_redirect('*/*/');
    }
}
