<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\AccountsInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Accounts extends \Magento\Framework\Model\AbstractModel implements AccountsInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'marketplace_rakuten_accounts';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_rakuten_accounts';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_rakuten_accounts';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Mangoit\RakutenConnector\Model\ResourceModel\Accounts');
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

    // /**
    //  * Get Country.
    //  *
    //  * @return varchar
    //  */
    // public function getCountry()
    // {
    //     return $this->getData(self::COUNTRY);
    // }

    // /**
    //  * Set Country.
    //  */
    // public function setCountry($country)
    // {
    //     return $this->setData(self::COUNTRY, $country);
    // }

    /**
     * Get SellerId.
     *
     * @return varchar
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set SellerId.
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    // /**
    //  * Get MarketplaceId.
    //  *
    //  * @return varchar
    //  */
    // public function getMarketplaceId()
    // {
    //     return $this->getData(self::MARKETPLACE_ID);
    // }

    // /**
    //  * Set marketplaceId.
    //  */
    // public function setMarketplaceId($marketplaceId)
    // {
    //     return $this->setData(self::MARKETPLACE_ID, $marketplaceId);
    // }
    
    // /**
    //  * Get AccessKeyId.
    //  *
    //  * @return varchar
    //  */
    // public function getAccessKeyId()
    // {
    //     return $this->getData(self::ACCESS_KEY_ID);
    // }

    // /**
    //  * Set AccessKeyId.
    //  */
    // public function setAccessKeyId($accessKeyId)
    // {
    //     return $this->setData(self::ACCESS_KEY_ID, $accessKeyId);
    // }

    /**
     * Get SecretKey.
     *
     * @return varchar
     */
    public function getSecretKey()
    {
        return $this->getData(self::SECRET_KEY);
    }

    /**
     * Set SecretKey.
     */
    public function setSecretKey($secretKey)
    {
        return $this->setData(self::SECRET_KEY, $secretKey);
    }

    // /**
    //  * Get currency code.
    //  *
    //  * @return varchar
    //  */
    // public function getCurrencyCode()
    // {
    //     return $this->getData(self::CURRENCY_CODE);
    // }

    // /**
    //  * Set currency code.
    //  */
    // public function setCurrencyCode($currencyCode)
    // {
    //     return $this->setData(self::CURRENCY_CODE, $currencyCode);
    // }

    // /**
    //  * Get currency rate.
    //  *
    //  * @return varchar
    //  */
    // public function getCurrencyRate()
    // {
    //     return $this->getData(self::CURRENCY_RATE);
    // }

    // /**
    //  * Set currency code.
    //  */
    // public function setCurrencyRate($currencyRate)
    // {
    //     return $this->setData(self::CURRENCY_RATE, $currencyRate);
    // }

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

    // /**
    // * get amzSellerId.
    // * @return $this
    // */
    // public function getAmzSellerId()
    // {
    //     return $this->getData(self::AMZ_SELLER_ID);
    // }
    // 
    // /**
    //  * set amzSellerId.
    //  * @return $this
    //  */
    // public function setAmzSellerId($amzSellerId)
    // {
    //     return $this->setData(self::AMZ_SELLER_ID, $amzSellerId);
    // }

    // /**
    //  * get listingReportId.
    //  * @return $this
    //  */
    // public function getListingReportId()
    // {
    //     return $this->getData(self::LISTING_REPORT_ID);
    // }
    
    // /**
    //  * set listingReportId.
    //  * @return $this
    //  */
    // public function setListingReportId($listingReportId)
    // {
    //     return $this->setData(self::LISTING_REPORT_ID, $listingReportId);
    // }

    // /**
    //  * get invendtoryReportId.
    //  * @return $this
    //  */
    // public function getInventoryReportId()
    // {
    //     return $this->getData(self::INVENTORY_REPORT_ID);
    // }

    // /**
    //  * set invendtoryReportId.
    //  * @return $this
    //  */
    // public function setInventoryReportId($invendtoryReportId)
    // {
    //     return $this->setData(self::INVENTORY_REPORT_ID, $invendtoryReportId);
    // }
}
