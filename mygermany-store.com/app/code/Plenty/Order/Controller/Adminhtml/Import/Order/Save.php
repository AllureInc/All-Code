<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Controller\Adminhtml\Import\Order;

use Plenty\Core\Controller\Adminhtml\Profile as ProfileAction;

/**
 * Class Save
 * @package Plenty\Order\Controller\Adminhtml\Import\Order
 */
class Save extends ProfileAction
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
