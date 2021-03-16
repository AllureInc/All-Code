<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;
/**
 * Class Index
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class Index extends \Plenty\Core\Controller\Adminhtml\Profile
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        /** @var \Magento\Framework\View\Result\PageFactory $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Plenty_Core::plenty_core_profile');
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->addBreadcrumb(__('Profiles'), __('Profiles'));
        $resultPage->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        $this->_getSession()->unsProfileData();
        $this->_getSession()->unsProfileFormData();

        return $resultPage;
    }
}