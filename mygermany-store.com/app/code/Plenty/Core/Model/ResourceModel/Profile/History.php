<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile;

use Plenty\Core\Model\ResourceModel\AbstractModel;
use Plenty\Core\Setup\SchemaInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Source\Status;
use Plenty\Core\Model\Profile;

/**
 * Class History
 * @package Plenty\Core\Model\ResourceModel\Profile
 */
class History extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::CORE_PROFILE_HISTORY, HistoryInterface::ENTITY_ID);
    }

    /**
     * @param $table
     * @return $this
     */
    public function _truncateTable($table)
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($table);
        } else {
            $this->getConnection()->truncateTable($table);
        }
        return $this;
    }

    /**
     * @param Profile $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingProfileHistory(Profile $profile)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from($this->getMainTable())
            ->where('profile_id = ?', $profile->getId())
            ->where('status = ?', Status::PENDING);
        $result = $adapter->fetchOne($select);
        return $result;
    }

    /**
     * @param Profile\History $object
     * @param array $bind
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update(Profile\History $object, array $bind)
    {
        $this->getConnection()
            ->update(
                $this->getMainTable(),
                $bind,
                ['entity_id = ?' => $object->getId()]
            );
        return $this;
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addRecord(array $data)
    {
        $this->getConnection()
            ->insertMultiple($this->getMainTable(), $data);
    }
}