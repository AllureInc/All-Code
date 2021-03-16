<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Webkul\MpAmazonConnector\Api\Data\AmazonTempDataInterface;
use Magento\Framework\DataObject\IdentityInterface;

class AmazonTempData extends \Magento\Framework\Model\AbstractModel implements AmazonTempDataInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'marketplace_amazon_tempdata';

    /**
     * @var string
     */
    public $_cacheTag = 'marketplace_amazon_tempdata';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    public $_eventPrefix = 'marketplace_amazon_tempdata';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAmazonConnector\Model\ResourceModel\AmazonTempData');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get ItemId.
     *
     * @return varchar
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Set ItemId.
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

   /**
    * Get ItemType.
    * @return string
    */
    public function getItemType()
    {
        return $this->getData(self::ITEM_TYPE);
    }

   /**
    * set ItemType.
    * @return $this
    */
    public function setItemType($itemType)
    {
        return $this->setData(self::ITEM_TYPE, $itemType);
    }

    /**
     * Get ProductData.
     *
     * @return varchar
     */
    public function getItemData()
    {
        return $this->getData(self::ITEM_DATA);
    }

    /**
     * Set ProductData.
     */
    public function setItemData($itemData)
    {
        return $this->setData(self::ITEM_DATA, $productData);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get MageAmzAccountId.
     * @return string
     */
    public function getSellerId()
    {
        return $this->getData(self::seller_id);
    }

    /**
     * set amzAccountId.
     * @return $this
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::seller_id, $sellerId);
    }
}
