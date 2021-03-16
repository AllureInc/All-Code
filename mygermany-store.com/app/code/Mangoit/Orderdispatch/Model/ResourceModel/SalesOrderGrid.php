<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 */
namespace Mangoit\Orderdispatch\Model\ResourceModel;

/**
 * Index resource
 */
class SalesOrderGrid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sales_order_grid', 'entity_id');
    }
}