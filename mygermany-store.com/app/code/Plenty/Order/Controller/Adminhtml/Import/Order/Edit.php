<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Controller\Adminhtml\Import\Order;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class Edit
 * @package Plenty\Order\Controller\Adminhtml\Import\Order
 */
class Edit extends Profile
{
    /**
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Plenty_Core::plenty_core_profile')
            ->addBreadcrumb(__('Plenty Profile'), __('Plenty Profile'))
            ->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));
        return $resultPage;
    }

    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $profile = $this->_initCurrentProfile();

        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Profile') : __('New Profile'),
            $id ? __('Edit Profile') : __('New Profile')
        );
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()
            ->prepend($profile->getName() ?? __('New Profile'));

        return $resultPage;
    }
}