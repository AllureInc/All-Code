<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Core\Api\Data\ProfileInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Profile
 */
class Collection extends AbstractCollection
{
    /**
     * Alias for main table
     */
    const MAIN_TABLE_ALIAS = 'e';

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Resource constructor
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\Profile::class, \Plenty\Core\Model\ResourceModel\Profile::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(ProfileInterface::ENTITY_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(ProfileInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }

    /**
     * @return $this
     */
    public function addAvailabilityFilter()
    {
        $this->addFieldToFilter(ProfileInterface::IS_ACTIVE, 1);
        $this->addFieldToFilter(ProfileInterface::CRONTAB, ['notnull' => true]);
        return $this;
    }
}