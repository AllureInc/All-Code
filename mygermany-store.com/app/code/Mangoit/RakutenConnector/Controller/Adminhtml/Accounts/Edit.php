<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

use Magento\Framework\Locale\Resolver;
use Mangoit\RakutenConnector\Model\AccountsFactory;
use Magento\Framework\Registry;
use Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

class Edit extends Accounts
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountsFactory $accountsFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

   /**
    * Init actions
    *
    * @return \Magento\Backend\Model\View\Result\Page
    */
    private function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mangoit_RakutenConnector::amazon_menu')
            ->addBreadcrumb(__('Lists'), __('Lists'))
            ->addBreadcrumb(__('Manage Info'), __('Manage Info'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $amzAccountsModel=$this->accountsFactory->create();
        if ($id) {
            $amzAccountsModel->load($id);
            if (!$amzAccountsModel->getEntityId()) {
                $this->messageManager->addError(__('This Rakuten account no longer exists.'));
                $this->_redirect('dropship/*/');
                return;
            }
        }

        $this->coreRegistry->register('amazon_user', $amzAccountsModel);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Info') : __('New Info'),
            $id ? __('Edit info') : __('New Info')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ?__('Edit Rakuten Account') : __('New Rakuten Account'));

        return $resultPage;
    }
}
