<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 */
namespace Mangoit\Marketplace\Model\ResourceModel\Sensitiveattrs;

/**
 * Garments Collection
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
        $this->_init('Mangoit\Marketplace\Model\Sensitiveattrs', 'Mangoit\Marketplace\Model\ResourceModel\Sensitiveattrs');
    }
}
