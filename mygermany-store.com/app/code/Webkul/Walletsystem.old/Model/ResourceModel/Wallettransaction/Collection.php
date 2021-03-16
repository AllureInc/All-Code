<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\ResourceModel\Wallettransaction;

use \Webkul\Walletsystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\Walletsystem\Model\Wallettransaction',
            'Webkul\Walletsystem\Model\ResourceModel\Wallettransaction'
        );
        $this->addFilterToMap(
            'entity_id',
            'main_table.entity_id'
        )->addFilterToMap(
            'customer_id',
            'main_table.customer_id'
        )->addFilterToMap(
            'status',
            'main_table.status'
        );
    }
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    public function getMonthlyTransactionDetails($customerId, $firstDay, $lastDay)
    {
        $query = "select admincredit,cashbackamount,customercredits,rechargewallet,".
        "refundamount,refundwalletorder,usedwallet,admindebit,transfertocustomer,".
        "(refundwalletorder+usedwallet+admindebit+transfertocustomer) as totaldebit,".
        "(admincredit+cashbackamount+customercredits+rechargewallet+refundamount) as totalcredit ".
        "from(select customer_id ,transaction_at,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=3 AND action='credit') as admincredit,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=3 AND action='debit') as admindebit,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=1 AND action='credit') as cashbackamount,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=4 AND action='credit') as customercredits,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=4 AND action='debit') as transfertocustomer,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=0 AND action='debit') as usedwallet,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=0 AND action='credit') as rechargewallet,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=2 AND action='credit') as refundamount,".
        "(select COALESCE(sum(amount), 0) from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' AND sender_type=2 AND action='debit') as refundwalletorder".
        " from wk_ws_wallet_transaction where customer_id=".$customerId.
        " AND status=1 AND transaction_at >= '".$firstDay."' AND transaction_at <= '".$lastDay.
        "' GROUP BY customer_id)wallet1";
        $connection = $this->getConnection();
        $returnData = $connection->fetchAll($query);
        return $returnData;
    }
}
