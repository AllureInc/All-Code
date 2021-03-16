<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class Delete extends Profile
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->_profileRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The profile has been deleted.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error was encountered while performing delete operation.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a profile to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}