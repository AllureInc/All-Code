<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class Edit
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class Edit extends Profile
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Plenty_Core::plenty_core_profile')
            ->addBreadcrumb(__('Plenty Profile'), __('Plenty Profile'))
            ->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $profile = $this->_initCurrentProfile();
        if ($id = $this->getRequest()->getParam('id')) {
            $profileUrl = $this->_profileEntityType->getRouter($profile);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath("{$profileUrl}/edit",
                ['id' => $id, 'section' => "{$profile->getEntity()}_{$profile->getAdaptor()}"]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Profile') : __('New Profile'),
            $id ? __('Edit Profile') : __('New Profile')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Profile'));

        return $resultPage;
    }
}