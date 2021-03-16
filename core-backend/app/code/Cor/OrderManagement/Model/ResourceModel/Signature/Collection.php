<?php
/**
 * Copyright © 2018 Magento. All rights reserved.
 */
namespace Cor\OrderManagement\Model\ResourceModel\Signature;

/**
 * Customer Signature Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Cor\OrderManagement\Model\Signature', 'Cor\OrderManagement\Model\ResourceModel\Signature');
    }
}
