<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'plenty_item_import_item_salesprice_id';


    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\Import\Item\SalesPrice::class, \Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice::class);
    }

}