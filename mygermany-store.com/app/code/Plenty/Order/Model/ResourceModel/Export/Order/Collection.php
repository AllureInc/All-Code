<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\ResourceModel\Export\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Order\Api\Data\Export\OrderInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Order\Model\ResourceModel\Export\Order
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OrderInterface::ENTITY_ID;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Order\Model\Export\Order::class, \Plenty\Order\Model\ResourceModel\Export\Order::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(OrderInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(OrderInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }
}