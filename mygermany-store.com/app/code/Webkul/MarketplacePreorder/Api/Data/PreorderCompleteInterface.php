<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Api\Data;

interface PreorderCompleteInterface
{
    const ENTITY_ID             = 'id';
    const ORDER_ITEM_ID         = 'order_item_id';
    const QUOTE_ITEM_ID         = 'quote_item_id';
    const ORDER_ID              = 'order_id';
    const CUSTOMER_ID           = 'customer_id';
    const PRODUCT_ID            = 'product_id';
    const QTY                   = 'qty';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderItemId();

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteItemId();

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get Produt ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get qty
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Order Item ID
     *
     * @return int|null
     */
    public function setOrderItemId($item);

    /**
     * Set Quote ID
     *
     * @return int|null
     */
    public function setQuoteItemId($item);

    /**
     * Set Order ID
     *
     * @return int|null
     */
    public function setOrderId($orderId);

    /**
     * Set Customer ID
     *
     * @return int|null
     */
    public function setCustomerId($customerId);

    /**
     * Set Produt ID
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set qty
     *
     * @return int|null
     */
    public function setQty($qty);
}
