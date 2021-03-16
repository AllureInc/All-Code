<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\ResourceModel\Auth;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Auth
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'plenty_core_auth_id';


    protected function _construct()
    {
        $this->_init('Plenty\Core\Model\Auth', 'Plenty\Core\Model\ResourceModel\Auth');
    }
}