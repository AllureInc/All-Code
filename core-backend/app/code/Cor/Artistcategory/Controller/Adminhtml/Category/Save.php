<?php
namespace Cor\Artistcategory\Controller\Adminhtml\Category;
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
            $model = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            } 
            if (!$this->categoryExist($id, $data['category_name'])) 
            {
                $model->setData($data);            
                try {
                    $model->save();
                    $this->messageManager->addSuccess(__('The category has been saved.'));
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
                    $this->messageManager->addException($e, __('Something went wrong while saving the category.'));
                }
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;                 
            } else {
                $this->messageManager->addError(__('The category is already exist'));
                if ( ($id != 0) || ($id != null) ) {
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return; 
                } else {
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/category/new');
                    return;
                }
            }
        }
        $this->_redirect('*/*/');
    }

    public function categoryExist($id, $category)
    {
        if ($id == '') {
            $id = 0;
        }
        $model = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
        $data = $model->getCollection()->getData();
        foreach ($data as $item) {
            if ($item['category_name'] == $category) {
                if ($id != $item['id']) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }
}
