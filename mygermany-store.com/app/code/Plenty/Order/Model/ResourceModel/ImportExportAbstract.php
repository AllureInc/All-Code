<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class ImportExportAbstract
 * @package Plenty\Order\Model\ResourceModel
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
     * Resource constructor.
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
    public function updateStatus($status = 'pending', $entries =[])
    {
        $data = ['status' => $status];
        $where = empty($entries) ? '' : ['entity_id in(?)' => $entries];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
        return $this;
    }

    /**
     * @param array $id
     * @param array $bind
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRecord(array $id, array $bind)
    {
        $this->getConnection()
            ->update($this->getMainTable(), $bind, ['variation_id IN(?)' => $id]);
        return $this;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateMultipleRecords(array $data, array $fields = [])
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