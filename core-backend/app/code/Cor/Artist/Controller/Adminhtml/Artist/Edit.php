<?php
namespace Cor\Artist\Controller\Adminhtml\Artist;

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
        $model = $this->_objectManager->create('Cor\Artist\Model\Artist');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $title = "New Artist";
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This artist no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $title = $model->getArtistName();
        }
        $registryObject->register('cor_artist', $model);
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->setActiveMenu('Cor_Artistcategory::cor_category_index');
        $this->resultPage ->getConfig()->getTitle()->set((__($title)));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
