<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\ResourceModel\Export;

use Plenty\Order\Model\ResourceModel\ImportExportAbstract;
use Plenty\Order\Setup\SchemaInterface;
use Plenty\Order\Api\Data\Export\OrderInterface;

/**
 * Class Order
 * @package Plenty\Order\Model\ResourceModel\Export
 */
class Order extends ImportExportAbstract
{
    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(SchemaInterface::EXPORT_ORDER_TABLE, OrderInterface::ENTITY_ID);
    }

    public function saveOrder(array $data, array $fields = [])
    {
        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $data, $fields);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

}