<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Trait ManageTrait
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
trait ManageTrait
{
    /**
     * Helper for redirect to index page
     *
     * @return ResultInterface|ResponseInterface
     */
    protected function redirectToIndex()
    {
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    /**
     * Helper for redirect to edit page
     *
     * @param int|string $id
     * @return ResultInterface|ResponseInterface
     */
    protected function redirectToEdit($id)
    {
        return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['choice_id' => $id]);
    }

    /**
     * Helper for redirect to new page
     *
     * @return ResultInterface|ResponseInterface
     */
    protected function redirectToNew()
    {
        return $this->resultRedirectFactory->create()->setPath('*/*/new');
    }

    /**
     * Helper for forwarding to edit page
     *
     * @return ResultInterface|ResponseInterface
     */
    protected function forwardToEdit()
    {
        /* @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
