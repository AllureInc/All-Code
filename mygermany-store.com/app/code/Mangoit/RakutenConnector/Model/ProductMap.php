<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\ProductMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class ProductMap extends \Magento\Framework\Model\AbstractModel implements ProductMapInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'marketplace_rakuten_mapped_product';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_rakuten_mapped_product';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_rakuten_mapped_product';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Mangoit\RakutenConnector\Model\ResourceModel\ProductMap');
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
     * Get AmazonProId.
     *
     * @return varchar
     */
    public function getRakutenProId()
    {
        return $this->getData(self::RAKUTEN_PRO_ID);
    }

    /**
     * Set AmazonProId.
     */
    public function setRakutenProId($amazonProId)
    {
        return $this->setData(self::RAKUTEN_PRO_ID, $amazonProId);
    }

    /**
     * Get Name.
     *
     * @return varchar
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set Name.
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get ProductType.
     *
     * @return varchar
     */
    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    /**
     * Set ProductType.
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * Get MagentoProId.
     *
     * @return varchar
     */
    public function getMagentoProId()
    {
        return $this->getData(self::MAGENTO_PRO_ID);
    }

    /**
     * Set MagentoProId.
     */
    public function setMagentoProId($magentoProId)
    {
        return $this->setData(self::MAGENTO_PRO_ID, $magentoProId);
    }

    /**
     * Get MageCatId.
     *
     * @return varchar
     */
    public function getMageCatId()
    {
        return $this->getData(self::MAGE_CAT_ID);
    }

    /**
     * Set MageCatId.
     */
    public function setMageCatId($mageCatId)
    {
        return $this->setData(self::MAGE_CAT_ID, $mageCatId);
    }

    /**
     * Get ChangeStatus.
     *
     * @return varchar
     */
    public function getChangeStatus()
    {
        return $this->getData(self::CHANGE_STATUS);
    }

    /**
     * Set ChangeStatus.
     */
    public function setChangeStatus($changeStatus)
    {
        return $this->setData(self::CHANGE_STATUS, $changeStatus);
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
     * Get submissionId.
     * @return int|null
     */
    public function getFeedsubmissionId()
    {
        return $this->getData(self::FEEDSUBMISSION_ID);
    }

    /**
     * set submissionId.
     * @return $this
     */
    public function setFeedsubmissionId($submissionId)
    {
    }

    /**
     * Get exportStatus.
     * @return int|null
     */
    public function getExportStatus()
    {
        return $this->getData(self::EXPORT_STATUS);
    }

    /**
     * set exportStatus.
     * @return $this
     */
    public function setExportStatus($exportStatus)
    {
        return $this->setData(self::EXPORT_STATUS, $exportStatus);
    }

    /**
     * Get errorStatus.
     * @return int|null
     */
    public function getErrorStatus()
    {
        return $this->getData(self::ERROR_STATUS);
    }

    /**
     * set errorStatus.
     * @return $this
     */
    public function setErrorStatus($errorStatus)
    {
        return $this->setData(self::ERROR_STATUS, $errorStatus);
    }

    /**
     * Get proStatus.
     * @return int|null
     */
    public function getProStatusAtRkt()
    {
        return $this->getData(self::PRO_STATUS_AT_RKT);
    }

    /**
     * set proStatus.
     * @return $this
     */
    public function setProStatusAtRkt($proStatus)
    {
        return $this->setData(self::PRO_STATUS_AT_RKT, $proStatus);
    }

    /**
     * Get sellerid.
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * set seller id.
     * @return $this
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }
}
