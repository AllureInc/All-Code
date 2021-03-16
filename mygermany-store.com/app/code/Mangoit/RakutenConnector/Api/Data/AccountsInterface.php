<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Api\Data;

interface AccountsInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    // const COUNTRY = 'country';
    // const MARKETPLACE_ID = 'marketplace_id';
    const SELLER_ID = 'seller_id';
    const CREATED_AT = 'created_at';
    // const CURRENCY_CODE = 'currency_code';
    // const CURRENCY_RATE = 'currency_rate';
    // const ACCESS_KEY_ID = 'access_key_id';
    const SECRET_KEY = 'secret_key';
    // const LISTING_REPORT_ID = 'listing_report_id';
    // const INVENTORY_REPORT_ID = 'inventory_report_id';
    // const AMZ_SELLER_ID = 'amz_seller_id';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

   // /**
   //  * Get Country.
   //  * @return string
   //  */
   //  public function getCountry();

   // /**
   //  * set Country.
   //  * @return $this
   //  */
   //  public function setCountry($country);

   // /**
   //  * Get MarketplaceId.
   //  * @return string
   //  */
   //  public function getMarketplaceId();

   // /**
   //  * set MarketplaceId.
   //  * @return $this
   //  */
   //  public function setMarketplaceId($marketplaceId);

   /**
    * Get SellerId.
    * @return string
    */
    public function getSellerId();

   /**
    * set SellerId.
    * @return $this
    */
    public function setSellerId($sellerId);

   // /**
   //  * Get AccessKeyId.
   //  * @return string
   //  */
   //  public function getAccessKeyId();

   // /**
   //  * set AccessKeyId.
   //  * @return $this
   //  */
   //  public function setAccessKeyId($accessKeyId);

   /**
    * Get SecretKey.
    * @return string
    */
    public function getSecretKey();

   /**
    * set SecretKey.
    * @return $this
    */
    public function setSecretKey($secretKey);

   // /**
   //  * Get currency code.
   //  * @return string
   //  */
   //  public function getCurrencyCode();

   // /**
   //  * set currency code.
   //  * @return $this
   //  */
   //  public function setCurrencyCode($currencyCode);

   // /**
   //  * Get currency code.
   //  * @return string
   //  */
   //  public function getCurrencyRate();

   // /**
   //  * set currency code.
   //  * @return $this
   //  */
   //  public function setCurrencyRate($currencyRate);

   /**
    * Get Status.
    * @return string
    */
    public function getStatus();

   /**
    * set Status.
    * @return $this
    */
    public function setStatus($status);

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

   // /**
   //  * get amzSellerId.
   //  * @return $this
   //  */
   //  public function getAmzSellerId();

   // /**
   //  * set amzSellerId.
   //  * @return $this
   //  */
   //  public function setAmzSellerId($amzSellerId);

   // /**
   //  * get listingReportId.
   //  * @return $this
   //  */
   //  public function getListingReportId();
    
   // /**
   //  * set listingReportId.
   //  * @return $this
   //  */
   //  public function setListingReportId($listingReportId);

   // /**
   //  * get invendtoryReportId.
   //  * @return $this
   //  */
   //  public function getInventoryReportId();

   // /**
   //  * set invendtoryReportId.
   //  * @return $this
   //  */
   //  public function setInventoryReportId($invendtoryReportId);
}
