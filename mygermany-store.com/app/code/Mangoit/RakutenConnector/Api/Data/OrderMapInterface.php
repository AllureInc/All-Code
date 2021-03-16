<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Api\Data;

interface OrderMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const RAKUTEN_ORDER_ID = 'rakuten_order_id';
    const SELLER_ID = 'seller_id';
    const MAGE_ORDER_ID = 'mage_order_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const PURCHASE_DATE = 'purchase_date';

    /**
     * Get ID.
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     * @return $this
     */
    public function setId($id);

    /**
     * Get amazon order id.
     * @return string
     */
    public function getRakutenOrderId();

    /**
     * set amazon order id.
     * @return $this
     */
    public function setRakutenOrderId($rakutenOrderId);

    /**
     * Get sellerId.
     * @return string
     */
    public function getSellerId();

    /**
     * set sellerId.
     * @return $this
     */
    public function setSellerId($sellerId);

    /**
     * Get MageOrderId.
     * @return string
     */
    public function getMageOrderId();

    /**
     * set MageOrderId.
     * @return $this
     */
    public function setMageOrderId($mageOrderId);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($created);

    /**
     * Get PurchaseDate.
     * @return string
     */
    public function getPurchaseDate();

    /**
     * set PurchaseDate.
     * @return $this
     */
    public function setPurchaseDate($purchaseDate);
}
