<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class Schedule
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class Schedule extends Profile
{
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