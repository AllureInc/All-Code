<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData;

/**
 * Interface AttributeDataInterface
 * @package Plenty\Item\Rest\AbstractData
 */
interface AttributeDataInterface
{
    const ENTITY_ATTRIBUTE                          = 'attribute';
    const ENTITY_MANUFACTURER                       = 'manufacturer';
    const ENTITY_PROPERTY                           = 'property';
    const ENTITY_PROPERTY_SELECTION                 = 'property_selection';

    // GENERAL ATTRIBUTES
    const LANG                                      = 'lang';

    // ATTRIBUTES
    const ID                                        = 'id';
    const BACKEND_NAME                              = 'backendName';
    const POSITION                                  = 'position';
    const IS_SURCHARGE_PERCENTAGE                   = 'isSurchargePercental';
    const IS_LINKABLE_TO_IMAGE                      = 'isLinkableToImage';
    const AMAZON_ATTRIBUTE                          = 'amazonAttribute';
    const FRUUGO_ATTRIBUTE                          = 'fruugoAttribute';
    const PIXMANIA_ATTRIBUTE                        = 'pixmaniaAttribute';
    const OTO_ATTRIBUTE                             = 'ottoAttribute';
    const GOOGLE_SHOPPING_ATTRIBUTE                 = 'googleShoppingAttribute';
    const NECKERMANN_ATTRIBUTE                      = 'neckermannAtEpAttribute';
    const TYPE_OF_SELECTION_IN_ONLINE_STORE         = 'typeOfSelectionInOnlineStore';
    const LA_REDOUTE_ATTRIBUTE                      = 'laRedouteAttribute';
    const IS_GROUPABLE                              = 'isGroupable';
    const ATTRIBUTE_NAMES                           = 'attributeNames';
    const VALUES                                    = 'values';
    const VALUE_NAMES                               = 'value_names';
    const MAPS                                      = 'maps';
    const UPDATED_AT                                = 'updatedAt';

    // MANUFACTURE ATTRIBUTE
    const NAME                                      = 'name';
    const LOGO                                      = 'logo';
    const URL                                       = 'url';
    const PIXMANIA_BRAND_ID                         = 'pixmaniaBrandId';
    const NECKERMANN_BRAND_ID                       = 'neckermannBrandId';
    const NECKERMANN_AT_EP__BRAND_ID                = 'neckermannAtEpBrandId';
    const LA_REDOUTE_BRAND_ID                       = 'laRedouteBrandId';
    const EXTERNAL_NAME                             = 'externalName';
    const STREET                                    = 'street';
    const HOUSE_NO                                  = 'houseNo';
    const POST_CODE                                 = 'postcode';
    const TOWN                                      = 'town';
    const COUNTRY_ID                                = 'countryId';
    const PHONE_NUMBER                              = 'phoneNumber';
    const FAX_NUMBER                                = 'faxNumber';
    const EMAIL                                     = 'email';
    const COMMENT                                   = 'comment';

    // PROPERTY
    const UNIT                                      = 'unit';
    const NAMES                                     = 'names';
    const PROPERTY_ID                               = 'propertyId';
    const PROPERTY_GROUP_ID                         = 'propertyGroupId';
    const VALUE_TYPE                                = 'valueType';
    const IS_SEARCHABLE                             = 'isSearchable';
    const IS_ORDER_PROPERTY                         = 'isOderProperty';
    const IS_SHOWN_ON_ITEM_PAGE                     = 'isShownOnItemPage';
    const IS_SHOWN_ON_ITEM_LIST                     = 'isShownOnItemList';
    const IS_SHOWN_AT_CHECKOUT                      = 'isShownAtCheckout';
    const IS_SHOWN_IN_PDF                           = 'isShownInPdf';
    const IS_SHOWN_AS_ADDITIONAL_COST               = 'isShownAsAdditionalCosts';
    const SURCHARGE                                 = 'surcharge';
    const GROUP                                     = 'group';
    const MARKET_COMPONENTS                         = 'marketComponents';
    const SELECTIONS                                = 'selections';
    const DESCRIPTION                               = 'description';
}