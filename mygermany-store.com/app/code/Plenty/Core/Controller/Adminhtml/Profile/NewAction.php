<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class NewAction
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class NewAction extends Profile
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}