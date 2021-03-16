<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Api\Data;

interface AmazonTempDataInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const ITEM_ID = 'item_id';
    const ITEM_TYPE = 'item_type';
    const ITEM_DATA = 'item_data';
    const CREATED_AT = 'created_at';
    const seller_id = 'seller_id';

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
    * Get ItemId.
    * @return string
    */
    public function getItemId();

   /**
    * set ItemId.
    * @return $this
    */
    public function setItemId($itemId);

   /**
    * Get ItemData.
    * @return string
    */
    public function getItemData();

   /**
    * set ItemData.
    * @return $this
    */
    public function setItemData($itemData);

   /**
    * Get ItemType.
    * @return string
    */
    public function getItemType();

   /**
    * set ItemType.
    * @return $this
    */
    public function setItemType($itemType);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

   /**
    * set CreatedAt.
    * @return $this
    */
    public function setCreatedAt($createdAt);

    /**
     * Get MageAmzAccountId.
     * @return string
     */
    public function getSellerId();

    /**
     * set amzAccountId.
     * @return $this
     */
    public function setSellerId($amzAccountId);
}
