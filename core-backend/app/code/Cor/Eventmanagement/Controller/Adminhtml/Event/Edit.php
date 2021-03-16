<?php
namespace Cor\Eventmanagement\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
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
        $model = $this->_objectManager->create('Cor\Eventmanagement\Model\Event');
        $title = 'New Event';
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This event no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $title = $model->getEventName();
        }
        $registryObject->register('cor_events', $model);
        $this->resultPage = $this->resultPageFactory->create();  
        $this->resultPage->setActiveMenu('Cor_Artistcategory::cor_category_index');
        $this->resultPage ->getConfig()->getTitle()->set((__($title)));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
