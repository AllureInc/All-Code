<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Adminhtml\Creditrules;

use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletcreditrulesFactory;

class Edit extends CreditrulesController
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $_resultPageFactory;
    /**
     * @var Webkul\Walletsystem\Model\WalletcreditrulesFactory
     */
    private $_walletcreditrulesFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        WalletcreditrulesFactory $walletcreditrulesFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_walletcreditrulesFactory = $walletcreditrulesFactory;
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Walletsystem::walletcreditrules')
            ->addBreadcrumb(__('Wallet System Credit Rule'), __('Wallet System Credit Rule'));
        return $resultPage;
    }
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_walletcreditrulesFactory
            ->create();
        if ($id) {
            $model->load($id);
            $flag = 1;
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('walletsystem/creditrules/creditrules');
            }
        }
        $data = $this->_session
                ->getFormData(true);

        if (isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        $this->_coreRegistry->register('wallet_creditrule', $model);
        $resultPage = $this->_initAction();
        if ($flag==1 && $id) {
            $resultPage->addBreadcrumb(__('Edit Wallet Credit Rule'), __('Edit Wallet Credit Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Credit Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Wallet Credit Rule'), __('Add Wallet Credit Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Credit Rule'));
        }
        return $resultPage;
    }
}
