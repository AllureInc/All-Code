<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Model\ResourceModel\SalesOrderGrid;

/**
 * Indexs Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
        $this->_init('Mangoit\Orderdispatch\Model\SalesOrderGrid', 'Mangoit\Orderdispatch\Model\ResourceModel\SalesOrderGrid');
    }
}