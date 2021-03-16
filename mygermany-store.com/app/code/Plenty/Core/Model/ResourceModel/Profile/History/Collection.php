<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile\History;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Profile\History
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = HistoryInterface::ENTITY_ID;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\Profile\History::class, \Plenty\Core\Model\ResourceModel\Profile\History::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(HistoryInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(HistoryInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }

    /**
     * @param $actionCode
     * @return $this
     */
    public function excludeActionCodeFromFilter($actionCode)
    {
        $this->addFieldToFilter(HistoryInterface::ACTION_CODE, ['neq' => $actionCode]);
        return $this;
    }
}