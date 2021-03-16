<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile\Schedule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Core\Api\Data\Profile\ScheduleInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Profile\Schedule
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\Profile\Schedule::class, \Plenty\Core\Model\ResourceModel\Profile\Schedule::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(ScheduleInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(ScheduleInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }

    public function orderByScheduledAt($dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->order(ScheduleInterface::SCHEDULED_AT, $dir);
        return $this;
    }
}