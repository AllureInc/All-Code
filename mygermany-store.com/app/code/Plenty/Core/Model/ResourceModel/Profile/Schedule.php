<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Plenty\Core\Model\ResourceModel\AbstractModel;
use Plenty\Core\Setup\SchemaInterface;
use Plenty\Core\Api\Data\Profile\ScheduleInterface;
use Plenty\Core\Model\Source\Status;
use Plenty\Core\Model\Profile;

/**
 * Class Schedule
 * @package Plenty\Core\Model\ResourceModel\Profile
 */
class Schedule extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::CORE_PROFILE_SCHEDULE, ScheduleInterface::ENTITY_ID);
    }

    /**
     * Sets new schedule status only if it's in the expected current status.
     *
     * If schedule is currently in $currentStatus, set it to $newStatus and
     * return true. Otherwise, return false.
     *
     * @param $scheduleId
     * @param $newStatus
     * @param $currentStatus
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function trySetJobStatusAtomic($scheduleId, $newStatus, $currentStatus)
    {
        $connection = $this->getConnection();
        $result = $connection->update(
            $this->getMainTable(),
            [ScheduleInterface::STATUS => $newStatus],
            [ScheduleInterface::ENTITY_ID.' = ?' => $scheduleId,
                ScheduleInterface::STATUS.' = ?' => $currentStatus]
        );
        if ($result == 1) {
            return true;
        }
        return false;
    }

    /**
     * Sets schedule status only if no existing schedules with the same job code
     * have that status.  This is used to implement locking for cron jobs.
     *
     * If the schedule is currently in $currentStatus and there are no existing
     * schedules with the same job code and $newStatus, set the schedule to
     * $newStatus and return true. Otherwise, return false.
     *
     * @param $scheduleId
     * @param $newStatus
     * @param $currentStatus
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function trySetJobUniqueStatusAtomic($scheduleId, $newStatus, $currentStatus)
    {
        $connection = $this->getConnection();

        // this condition added to avoid cron jobs locking after incorrect termination of running job
        $match = $connection->quoteInto(
            'existing.job_code = current.job_code ' .
            'AND (existing.executed_at > UTC_TIMESTAMP() - INTERVAL 1 DAY OR existing.executed_at IS NULL) ' .
            'AND existing.status = ?',
            $newStatus
        );

        $selectIfUnlocked = $connection->select()
            ->joinLeft(
                ['existing' => $this->getMainTable()],
                $match,
                ['status' => new \Zend_Db_Expr($connection->quote($newStatus))]
            )
            ->where('current.entity_id = ?', $scheduleId)
            ->where('current.status = ?', $currentStatus)
            ->where('existing.entity_id IS NULL');

        $update = $connection->updateFromSelect($selectIfUnlocked, ['current' => $this->getMainTable()]);
        $result = $connection->query($update)->rowCount();

        if ($result == 1) {
            return true;
        }
        return false;
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
     * @param string $status
     * @param array $entries
     * @return $this|AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($status = Status::PENDING, $entries = [])
    {
        $data = ['status' => $status];
        $where = empty($entries) ? '' : ['entity_id in(?)' => $entries];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
        return $this;
    }

    /**
     * @param Profile\Schedule $object
     * @param array $bind
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update(Profile\Schedule $object, array $bind)
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
}