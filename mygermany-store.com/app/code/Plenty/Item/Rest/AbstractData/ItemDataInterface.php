<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData;

/**
 * Interface ItemDataInterface
 * @package Plenty\Item\Rest\Response
 */
interface ItemDataInterface
{
    const ID                                = 'id';
    const ITEM_ID                           = 'itemId';
    const VARIATION_ID                      = 'variationId';
    const MAIN_VARIATION_ID                 = 'mainVariationId';
    const EXTERNAL_ID                       = 'externalId';
    const NUMBER                            = 'number';
    const POSITION                          = 'position';
    const CONDITION                         = 'condition';
    const CONDITION_API                     = 'conditionApi';
    const ADD_CMS_PAGE                      = 'add_cms_page';
    const MANUFACTURER_ID                   = 'manufacturerId';
    const STORE_SPECIAL                     = 'storeSpecial';
    const AMAZON_FEDAS                      = 'amazonFedas';
    const STATUS                            = 'status';
    const IS_ACTIVE                         = 'isActive';
    const ITEM_TYPE                         = 'itemType';
    const BUNDLE_TYPE                       = 'bundleType';
    const STOCK_TYPE                        = 'stockType';
    const FLAG_ONE                          = 'flagOne';
    const FLAG_TWO                          = 'flagTwo';
    const OWNER_ID                          = 'ownerId';
    const FREE1                             = 'free1';
    const FREE2                             = 'free2';
    const FREE3                             = 'free3';
    const FREE4                             = 'free4';
    const FREE5                             = 'free5';
    const FREE6                             = 'free6';
    const FREE7                             = 'free7';
    const FREE8                             = 'free8';
    const FREE9                             = 'free9';
    const FREE10                            = 'free10';
    const FREE11                            = 'free11';
    const FREE12                            = 'free12';
    const FREE13                            = 'free13';
    const FREE14                            = 'free14';
    const FREE15                            = 'free15';
    const FREE16                            = 'free16';
    const FREE17                            = 'free17';
    const FREE18                            = 'free18';
    const FREE19                            = 'free19';
    const FREE20                            = 'free20';
    const CUSTOMS_TARIFF_NUMBER             = 'customsTariffNumber';
    const REVENUE_ACCOUNT                   = 'revenueAccount';
    const COUPON_RESTRICTION                = 'couponRestriction';
    const AGE_RESTRICTION                   = 'ageRestriction';
    const MAX_ORDER_QTY                     = 'maximumOrderQuantity';
    const IS_SUBSCRIPTION                   = 'isSubscribable';
    const RAKUTEN_CATEGORY_ID               = 'rakutenCategoryId';
    const IS_SHIPPING_PACKAGE               = 'isShippingPackage';
    const IS_SERIAL_NUMBER                  = 'isSerialNumber';
    const MANUFACTURER_COUNTRY_ID           = 'producingCountryId';
    const AMAZON_PRODUCT_TYPE               = 'amazonProductType';
    const EBAY_PRESET_ID                    = 'ebayPresetId';
    const EBAY_CATEGORY_ID                  = 'ebayCategory';
    const EBAY_CATEGORY_ID2                 = 'ebayCategory2';
    const EBAY_STORE_CATEGORY               = 'ebayStoreCategory';
    const EBAY_STORE_CATEGORY2              = 'ebayStoreCategory2';
    const AMAZON_FBA_PLATFORM               = 'amazonFbaPlatform';
    const FEEDBACK                          = 'feedback';
    const IS_SHIPPABLE_BY_AMAZON            = 'isShippableByAmazon';
    const SHIPPING_PROFILE                  = 'shipping_profiles';
    const ITEM_CROSS_SELLING                = 'itemCrossSelling';
    const ITEM_VARIATIONS                   = 'variations';
    const ITEM_IMAGES                       = 'itemImages';
    const ITEM_SHIPPING_PROFILES            = 'itemShippingProfiles';
    const ITEM_TEXTS                        = 'texts';
    const MESSAGE                           = 'message';
    const CREATED_AT                        = 'createdAt';
    const UPDATED_AT                        = 'updatedAt';
}