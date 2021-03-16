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

interface PreorderItemsInterface
{
    const ENTITY_ID             = 'id';
    const SELLER_ID             = 'seller_id';
    const ORDER_ID              = 'order_id';
    const ITEM_ID               = 'item_id';
    const PRODUCT_ID            = 'product_id';
    const PARENT_ID             = 'parent_id';
    const CUSTOMER_ID           = 'customer_id';
    const CUSTOMER_EMAIL        = 'customer_email';
    const PREORDER_PERCENT      = 'preorder_percent';
    const PAID_AMOUNT           = 'paid_amount';
    const REMAINING_AMOUNT      = 'remaining_amount';
    const QTY                   = 'qty';
    const TYPE                  = 'type';
    const STATUS                = 'status';
    const NOTIFY                = 'notify';
    const TIME                  = 'time';
    const ORDER_MODE            = 'order_mode';
    const COMPLETED_PREORDER_QTY= 'completed_preorder_qty';
    const TAX_CLASS_ID          = 'tax_class_id';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Get Item ID
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Get Produt ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get Parent ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get CustomerEmail
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Get PreorderPrecent
     *
     * @return int|null
     */
    public function getPreorderPrecent();

    /**
     * Get PaidAmount
     *
     * @return int|null
     */
    public function getPaidAmount();

    /**
     * Get RemainingAmount
     *
     * @return int|null
     */
    public function getRemainingAmount();

    /**
     * Get qty
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Get Type
     *
     * @return int|null
     */
    public function getType();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Notify
     *
     * @return int|null
     */
    public function getNotify();

    /**
     * Get Time
     *
     * @return string|null
     */
    public function getTime();

    /**
     * Get OrderMode
     *
     * @return string|null
     */
    public function getOrderMode();

    /**
     * Get completed preorder qty
     *
     * @return int|null
     */
    public function getCompletedPreorderQty();

     /**
     * Get Tax Class
     *
     * @return string|null
     */
    public function getTaxClassId();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Seller ID
     *
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Order ID
     *
     * @return int|null
     */
    public function setOrderId($orderId);

    /**
     * Set Item ID
     *
     * @return int|null
     */
    public function setItemId($itemId);

    /**
     * Set Produt ID
     *
     * @return int|null
     */
    public function setProductId($productId);

    /**
     * Set Parent ID
     *
     * @return int|null
     */
    public function setParentId($parentId);

    /**
     * Set Customer ID
     *
     * @return int|null
     */
    public function setCustomerId($customerId);

    /**
     * Set CustomerEmail
     *
     * @return string|null
     */
    public function setCustomerEmail($email);

    /**
     * Set PreorderPrecent
     *
     * @return int|null
     */
    public function setPreorderPrecent($percent);

    /**
     * Set PaidAmount
     *
     * @return int|null
     */
    public function setPaidAmount($paidAmount);

    /**
     * Set RemainingAmount
     *
     * @return int|null
     */
    public function setRemainingAmount($remaining);

    /**
     * Set qty
     *
     * @return int|null
     */
    public function setQty($qty);

    /**
     * Set Type
     *
     * @return int|null
     */
    public function setType($type);

    /**
     * Set Status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Notify
     *
     * @return int|null
     */
    public function setNotify($status);

    /**
     * Set Time
     *
     * @return string|null
     */
    public function setTime($time);

    /**
     * Set OrderMode
     *
     * @return string|null
     */
    public function setOrderMode($mode);

    /**
     * Set completed preorder qty
     *
     * @return int|null
     */
    public function setCompletedPreorderQty($completedQty);

     /**
     * Set Tax Class
     *
     * @return string|null
     */
    public function setTaxClassId($taxId);
}
