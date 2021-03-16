<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\ResourceModel\Import;

use Plenty\Stock\Model\ResourceModel\ImportExportAbstract;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Source;
use Magento\Framework\Data\Collection;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Inventory
 * @package Plenty\Stock\Model\ResourceModel\Import
 */
class Inventory extends ImportExportAbstract
{
    /**
     * DB Constructor.
     */
    protected function _construct()
    {
        $this->_init('plenty_stock_import_inventory', 'entity_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt($this->_date->gmtDate());
        }
        return $this;
    }

    /**
     * @param Profile $profile
     * @param Collection $data
     * @param array $fields
     * @return $this
     */
    public function saveStockData(
        Profile $profile, Collection $data, array $fields = []
    ) {

        $stockData = [];
        /** @var \Magento\Framework\DataObject $item */
        foreach ($data as $item) {
            $item->setData('profile_id', $profile->getId());
            $item->setData('status', Source\Status::PENDING);
            $item->setData('message', __('Collected.'));
            $item->setData('created_at', $this->_date->gmtDate());
            $item->setData('collected_at', $this->_date->gmtDate());
            $item->setData('processed_at', $this->_date->gmtDate());
            $stockData[] = $item->toArray();
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $stockData, $fields);
        } catch (\Exception $e) {
        }

        return $this;
    }
}