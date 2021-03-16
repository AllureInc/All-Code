<?php
/**
 * WalletCreditAmount Interface
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Api\Data;

interface WalletCreditAmountInterface
{
    const ENTITY_ID = 'entity_id';
    const AMOUNT = 'amount';
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setEntityId($id);
    /**
     * Get Amount
     *
     * @return int|null
     */
    public function getAmount();
    /**
     * Set Amount
     *
     * @param int $amount
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setAmount($amount);
    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();
    /**
     * Set Order ID
     *
     * @param int $ids
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setOrderId($ids);
    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();
    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface
     */
    public function setStatus($status);
}
