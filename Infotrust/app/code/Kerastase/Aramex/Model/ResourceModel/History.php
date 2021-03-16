<?php

namespace Kerastase\Aramex\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stock_reconciliation_history', 'id');
    }

    public function addRecord($sku, $old_qty,$new_qty, $comment, $update_date)
    {
        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ResourceConnection');
        $connection = $this->_resources->getConnection();

        $myTable = $this->_resources->getTableName('stock_reconciliation_history');
        $sql = "INSERT INTO " . $myTable . "(sku, old_qty, new_qty,comment, updated_at) VALUES ('$sku', $old_qty, $new_qty,'$comment', '$update_date')";
        $connection->query($sql);
    }


}