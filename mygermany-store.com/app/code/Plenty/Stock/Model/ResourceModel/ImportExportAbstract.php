<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Plenty\Core\Model\Source\Status;

/**
 * Class ImportExportAbstract
 * @package Plenty\Stock\Model\ResourceModel
 */
class ImportExportAbstract extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var Json
     */
    protected $_serializer;

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * ImportExportAbstract constructor.
     * @param Context $context
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        ?Json $serializer = null,
        ?string $connectionName = null
    ) {
        $this->_date = $dateTime;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * @param string $status
     * @param array $entries
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($status = Status::PENDING, $entries =[])
    {
        $data = ['status' => $status];
        $where = empty($entries) ? '' : ['entity_id in(?)' => $entries];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
        return $this;
    }

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRecord(array $bind, $where = '')
    {
        $this->getConnection()
            ->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @deprecated
     */
    public function updateMultipleRecords(array $data, array $fields = [])
    {
        $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
        return $this;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function addMultiple(array $data, array $fields = [])
    {
        $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function truncateTable()
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($this->getMainTable());
        } else {
            $this->getConnection()->truncateTable($this->getMainTable());
        }
        return $this;
    }

    /**
     * @param $field
     * @param $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeEntry($field, $ids)
    {
        $where = array($field.' IN (?)' => $ids);
        $this->getConnection()->delete($this->getMainTable(), $where);
    }
}