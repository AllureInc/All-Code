<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel;

/**
 * Class Profile
 * @package Plenty\Core\Model\ResourceModel
 */
class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('plenty_core_profile', 'entity_id');
    }

}