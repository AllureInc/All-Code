<?php
namespace Cor\Artist\Controller\Adminhtml\Artist;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {        
        $data = $this->getRequest()->getParams();
        if ($data) {
            if (isset($data['wnine_received']) && $data['wnine_received'] == true) {
                $data['wnine_received'] = 1;
            } else {
                $data['wnine_received'] = 0;
            }
            $model = $this->_objectManager->create('Cor\Artist\Model\Artist');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }           
            try {
                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccess(__('The artist has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }
            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('*/*/');
    }
}
