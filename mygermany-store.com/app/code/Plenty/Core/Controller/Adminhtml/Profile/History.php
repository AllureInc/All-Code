<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class History
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class History extends Profile
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
        $this->_initCurrentProfile();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}