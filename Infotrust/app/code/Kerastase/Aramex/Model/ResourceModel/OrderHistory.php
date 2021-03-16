<?php

namespace Kerastase\Aramex\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderHistory extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stock_reconciliation_history', 'id');
    }

    public function addRecord($order_id, $status,$action, $cron_runing,$comments ,$created_at)
    {
        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ResourceConnection');
        $connection = $this->_resources->getConnection();

	$comments = addslashes($comments);

        $myTable = $this->_resources->getTableName('order_changes_logs');
        $sql = "INSERT INTO " . $myTable . "(order_id, status,action,cron_runing, comments,created_at) VALUES ($order_id, '$status', '$action','$cron_runing','$comments','$created_at')";
        $connection->query($sql);
    }


}
