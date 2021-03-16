<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class Save
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class Save extends Profile
{
    /**
     * Saves profile
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_save();
    }
}
