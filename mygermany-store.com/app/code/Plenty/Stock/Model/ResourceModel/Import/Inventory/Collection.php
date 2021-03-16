<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\ResourceModel\Import\Inventory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Stock\Api\Data\Import\InventoryInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Stock\Model\ResourceModel\Import\Inventory
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = InventoryInterface::ENTITY_ID;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Stock\Model\Import\Inventory::class, \Plenty\Stock\Model\ResourceModel\Import\Inventory::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(InventoryInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(InventoryInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }
}