<?php
namespace Cor\Artistcategory\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $title = "New Artist Category";
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $title = $model->getCategoryName();
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('cor_artistcategory_registry', $model);
        $this->resultPage = $this->resultPageFactory->create();  
        $this->resultPage->setActiveMenu('Cor_Artistcategory::cor_category_index');
        $this->resultPage ->getConfig()->getTitle()->set((__($title)));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
