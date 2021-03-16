<?php
namespace Cor\Eventmanagement\Controller\Adminhtml\Event;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
                $banner = $this->_objectManager->get('Cor\Eventmanagement\Model\Event')->load($id);
                $banner->delete();
                $this->messageManager->addSuccess(
                    __('The event has been deleted successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        $this->_redirect('*/*/');
    }
}
