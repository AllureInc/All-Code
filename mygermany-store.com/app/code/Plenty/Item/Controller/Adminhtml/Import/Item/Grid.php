<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Controller\Adminhtml\Import\Item;

use Plenty\Core\Controller\Adminhtml\Profile;

/**
 * Class Grid
 * @package Plenty\Item\Controller\Adminhtml\Import\Item
 */
class Grid extends Profile
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