<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Controller\Adminhtml\Import\Inventory;

use Plenty\Core\Controller\Adminhtml\Profile as ProfileAction;

/**
 * Class Save
 * @package Plenty\Stock\Controller\Adminhtml\Import\Inventory
 */
class Save extends ProfileAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|mixed|\Plenty\Core\Model\Profile
     */
    public function execute()
    {
        return $this->_save();
    }
}
