<?php
/**
 * Copyright © 2018 Cor. All rights reserved.
 */
namespace Cor\OrderManagement\Model\ResourceModel;
/**
 * Customer signature resource
 */
class Signature extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cor_order_customer_signature', 'id');
    }
}
