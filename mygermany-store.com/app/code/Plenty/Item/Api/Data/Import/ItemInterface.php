<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import;

use Plenty\Item\Model\Import\Attribute;

/**
 * Interface ItemInterface
 * @package Plenty\Item\Api\Data\Import
 */
interface ItemInterface
{
    const ENTITY_ID                     = 'entity_id';
    const PROFILE_ID                    = 'profile_id';
    const ITEM_ID                       = 'item_id';
    const VARIATION_ID                  = 'variation_id';
    const MAIN_VARIATION_ID             = 'main_variation_id';
    const EXTERNAL_ID                   = 'external_id';
    const SKU                           = 'sku';
    const STATUS                        = 'status';
    const IS_ACTIVE                     = 'is_active';
    const ITEM_TYPE                     = 'item_type';
    const PRODUCT_TYPE                  = 'product_type';
    const BUNDLE_TYPE                   = 'bundle_type';
    const STOCK_TYPE                    = 'stock_type';
    const ATTRIBUTE_SET                 = 'attribute_set';
    const FLAG_ONE                      = 'flag_one';
    const FLAG_TWO                      = 'flag_two';
    const POSITION                      = 'position';
    const CONDITION                     = 'condition';
    const CONDITION_API                 = 'condition_api';
    const OWNER_ID                      = 'owner_id';
    const MANUFACTURER_ID               = 'manufacturer_id';
    const MANUFACTURER_COUNTRY_ID       = 'manufacturer_country_id';
    const STORE_SPECIAL                 = 'store_special';
    const MAX_ORDER_QTY                 = 'max_order_qty';
    const IS_SUBSCRIPTION               = 'is_subscription';
    const RAKUTEN_CATEGORY_ID           = 'rakuten_category_id';
    const IS_SHIPPING_PACKAGE           = 'is_shipping_package';
    const IS_SERIAL_NUMBER              = 'is_serial_number';
    const ADD_CMS_PAGE                  = 'add_cms_page';
    const CUSTOMS_TARIFF_NO             = 'custom_tariff_no';
    const REVENUE_ACCOUNT               = 'revenue_account';
    const COUPON_RESTRICTION            = 'coupon_restriction';
    const AGE_RESTRICTION               = 'age_restriction';
    const FREE1                         = 'free1';
    const FREE2                         = 'free2';
    const FREE3                         = 'free3';
    const FREE4                         = 'free4';
    const FREE5                         = 'free5';
    const FREE6                         = 'free6';
    const FREE7                         = 'free7';
    const FREE8                         = 'free8';
    const FREE9                         = 'free9';
    const FREE10                        = 'free10';
    const FREE11                        = 'free11';
    const FREE12                        = 'free12';
    const FREE13                        = 'free13';
    const FREE14                        = 'free14';
    const FREE15                        = 'free15';
    const FREE16                        = 'free16';
    const FREE17                        = 'free17';
    const FREE18                        = 'free18';
    const FREE19                        = 'free19';
    const FREE20                        = 'free20';
    const AMAZON_PRODUCT_TYPE           = 'amazon_product_type';
    const AMAZON_FEDAS                  = 'amazon_fedas';
    const EBAY_PRESET_ID                = 'ebay_preset_id';
    const EBAY_CATEGORY_ID              = 'ebay_category_id';
    const EBAY_CATEGORY_ID2             = 'ebay_category_id2';
    const EBAY_STORE_CATEGORY           = 'ebay_store_category';
    const EBAY_STORE_CATEGORY2          = 'ebay_store_category2';
    const AMAZON_FBA_PLATFORM           = 'amazon_fba_platform';
    const FEEDBACK                      = 'feedback';
    const IS_SHIPPABLE_BY_AMAZON        = 'is_shippable_by_amazon';
    const ITEM_SHIPPING_PROFILES        = 'item_shipping_profiles';
    const SHIPPING_PROFILE              = 'shipping_profiles';
    const ITEM_CROSS_SELLING            = 'item_crosssells';
    const ITEM_VARIATIONS               = 'item_variations';
    const ITEM_IMAGES                   = 'item_images';
    const MESSAGE                       = 'message';
    const CREATED_AT                    = 'created_at';
    const UPDATED_AT                    = 'updated_at';
    const COLLECTED_AT                  = 'collected_at';
    const PROCESSED_AT                  = 'processed_at';

    const ITEM_RESPONSE                 = '_item_response';
    const VARIATION_RESPONSE            = '_variation_response';

    public function getProfileId();

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getStatus();

    public function getIsActive();

    public function getItemType();

    public function getProductType();

    public function getBundleType();

    public function getStockType();

    public function getAttributeSet();

    public function getFlagOne();

    public function getFlagTwo();

    public function getPosition();

    public function getCustomTariffNo();

    public function getRevenueAccount();

    public function getCondition();

    public function getConditionApi();

    public function getOwnerId();

    public function getManufacturerId();

    public function getManufacturerCountryId();

    public function getStoreSpecial();

    public function getCouponRestriction();

    public function getMaxOrderQty();

    public function getIsSubscription();

    public function getRakutenCategoryId();

    public function getIsShippingPackage();

    public function getIsSerialNumber();

    public function getAmazonFbaPlatform();

    public function getIsShippableByAmazon();

    public function getAmazonProductType();

    public function getAgeRestriction();

    public function getFeedBack();

    public function getFree1();

    public function getFree2();

    public function getFree3();

    public function getFree4();

    public function getFree5();

    public function getFree6();

    public function getFree7();

    public function getFree8();

    public function getFree9();

    public function getFree10();

    public function getShippingProfiles();

    public function getMessage();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    public function getProcessedAt();

    /**
     * @param null $variationId
     * @return \Plenty\Item\Api\Data\Import\Item\VariationInterface
     */
    public function getVariation($variationId = null);

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemVariationCollection();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getUsedVariations();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection
     */
    public function getItemProperties();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Media\Collection
     */
    public function getItemMediaImages();

    public function getItemCrossSells();

    public function getItemShippingProfiles();

    public function getItemEbayTitles();

    /**
     * @param $id
     * @return Attribute
     */
    public function getItemManufacturer($id);
}