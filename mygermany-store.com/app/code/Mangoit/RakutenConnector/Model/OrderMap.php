<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\OrderMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class OrderMap extends \Magento\Framework\Model\AbstractModel implements OrderMapInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'marketplace_rakuten_mapped_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_rakuten_mapped_order';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_rakuten_mapped_order';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Mangoit\RakutenConnector\Model\ResourceModel\OrderMap');
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get AmazonOrderId.
     *
     * @return varchar
     */
    public function getRakutenOrderId()
    {
        return $this->getData(self::RAKUTEN_ORDER_ID);
    }

    /**
     * Set AmazonOrderId.
     */
    public function setRakutenOrderId($rakutenOrderId)
    {
        return $this->setData(self::RAKUTEN_ORDER_ID, $rakutenOrderId);
    }

    /**
     * Get MageOrderId.
     *
     * @return varchar
     */
    public function getMageOrderId()
    {
        return $this->getData(self::MAGE_ORDER_ID);
    }

    /**
     * Set MageOrderId.
     */
    public function setMageOrderId($mageOrderId)
    {
        return $this->setData(self::MAGE_ORDER_ID, $mageOrderId);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
        return $this->getData(self::SELLER_ID);
    }

    /**
     * set amzAccountId.
     * @return $this
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get PurchaseDate.
     * @return string
     */
    public function getPurchaseDate()
    {
        return $this->getData(self::PURCHASE_DATE);
    }

    /**
     * set PurchaseDate.
     * @return $this
     */
    public function setPurchaseDate($purchaseDate)
    {
        return $this->setData(self::PURCHASE_DATE, $purchaseDate);
    }
}
