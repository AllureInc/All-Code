<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import;

use Plenty\Item\Model\Import\Attribute;

/**
 * Interface AttributeInterface
 * @package Plenty\Item\Api\Data\Import
 */
interface AttributeInterface
{
    const ENTITY_ATTRIBUTE                          = 'attribute';
    const ENTITY_MANUFACTURER                       = 'manufacturer';
    const ENTITY_PROPERTY                           = 'property';
    const ENTITY_PROPERTY_SELECTION                 = 'property_selection';

    // GENERAL ATTRIBUTES
    const LANG                                      = 'lang';

    // ATTRIBUTES
    const ENTITY_ID                                 = 'entity_id';
    const PROFILE_ID                                = 'profile_id';
    const PROFILE_ATTRIBUTE_ID                      = 'profile_attribute_id';
    const TYPE                                      = 'type';
    const ATTRIBUTE_ID                              = 'attribute_id';
    const MANUFACTURER_ID                           = 'manufacturer_id';
    const ATTRIBUTE_CODE                            = 'attribute_code';
    const VALUE_TYPE                                = 'value_type';
    const ENTRIES                                   = 'entries';
    const NAMES                                     = 'names';
    const VALUES                                    = 'values';
    const VALUE_NAMES                               = 'value_names';
    const BACKEND_NAME                              = 'backend_name';
    const POSITION                                  = 'position';
    const IS_SURCHARGE_PERCENTAGE                   = 'is_surcharge_percentage';
    const IS_LINKABLE_TO_IMAGE                      = 'is_linkable_to_image';
    const AMAZON_ATTRIBUTE                          = 'amazon_attribute';
    const FRUUGO_ATTRIBUTE                          = 'fruugo_attribute';
    const PIXMANIA_ATTRIBUTE                        = 'pixmania_attribute';
    const OTO_ATTRIBUTE                             = 'oto_attribute';
    const GOOGLE_SHOPPING_ATTRIBUTE                 = 'google_shopping_attribute';
    const NECKERMANN_ATTRIBUTE                      = 'neckermann_attribute';
    const TYPE_OF_SELECTION_IN_ONLINE_STORE         = 'type_of_selection_online_store';
    const LA_REDOUTE_ATTRIBUTE                      = 'la_redoute_attribute';
    const IS_GROUPABLE                              = 'is_groupable';
    const ATTRIBUTE_NAMES                           = 'attribute_names';
    const MAPS                                      = 'maps';
    const MESSAGE                                   = 'message';
    const CREATED_AT                                = 'created_at';
    const UPDATED_AT                                = 'updated_at';
    const COLLECTED_AT                              = 'collected_at';
    const PROCESSED_AT                              = 'processed_at';

    // MANUFACTURE ATTRIBUTE
    const NAME                                      = 'names';
    const LOGO                                      = 'logo';
    const URL                                       = 'url';
    const PIXMANIA_BRAND_ID                         = 'pixmania_brand_id';
    const NECKERMANN_BRAND_ID                       = 'neckermann_brand_id';
    const NECKERMANN_AT_EP__BRAND_ID                = 'neckermann_at_ep_brand_id';
    const LA_REDOUTE_BRAND_ID                       = 'la_redoute_brand_id';
    const EXTERNAL_NAME                             = 'external_name';
    const STREET                                    = 'street';
    const HOUSE_NO                                  = 'house_no';
    const POST_CODE                                 = 'postcode';
    const TOWN                                      = 'town';
    const COUNTRY_ID                                = 'country_id';
    const PHONE_NUMBER                              = 'phone';
    const FAX_NUMBER                                = 'fax';
    const EMAIL                                     = 'email';
    const COMMENT                                   = 'comment';

    // PROPERTY
    const PROPERTY_ID                               = 'property_id';
    const PROPERTY_CODE                             = 'property_code';
    const UNIT                                      = 'unit';
    const PROPERTY_GROUP_ID                         = 'property_group_id';
    const GROUP                                     = 'group';
    const MARKET_COMPONENTS                         = 'market_components';
    const SELECTIONS                                = 'selections';
    const IS_SEARCHABLE                             = 'is_searchable';
    const IS_ORDER_PROPERTY                         = 'is_order_property';
    const IS_SHOWN_ON_ITEM_PAGE                     = 'is_shown_on_item_page';
    const IS_SHOWN_ON_ITEM_LIST                     = 'is_shown_on_item_list';
    const IS_SHOWN_AT_CHECKOUT                      = 'is_shown_at_checkout';
    const IS_SHOWN_IN_PDF                           = 'is_shown_in_pdf';
    const IS_SHOWN_AS_ADDITIONAL_COST               = 'is_shown_as_additional_charge';
    const SURCHARGE                                 = 'surcharge';

    // PROPERTY SELECTION
    const PROPERTY_SELECTION_ID                     = 'selection_id';
    const DESCRIPTION                               = 'description';

    const BEHAVIOUR_APPEND                          = 'append';
    const BEHAVIOUR_REPLACE                         = 'replace';

    public function getProfileId() : int;

    public function getProfileAttributeId() : string ;

    public function getType() : string ;

    public function getPosition() : ?int ;

    public function getAttributeId() : ?int ;

    public function getPropertyId() : ?int;

    public function getManufacturerId() : ?int ;

    public function getAttributeCode() : ?string ;

    public function getPropertyCode() : ?string ;

    public function getPropertyGroupId() : ?int;

    public function getValueType() : ?string ;

    public function getEntries() : array ;

    public function getNames() : array ;

    public function getManufacturerName() : ?string ;

    public function getValues() : array ;

    public function getValueNames() : array ;

    public function getSelections() : array ;

    public function getMaps() : array ;

    public function getGroup() : array ;

    public function getMarketComponents() : array ;

    public function getMessage() : ?string ;

    public function getCreatedAt() : ?string;

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param $attributeCode
     * @return Attribute
     */
    public function getAttributeByCode($attributeCode) : Attribute;

    /**
     * @param $attributeCode
     * @param null $profileId
     * @return int|null
     */
    public function getAttributeIdByCode($attributeCode, $profileId = null);

    /**
     * @param $attributeId
     * @return mixed
     */
    public function getAttributeNamesById($attributeId);

    /**
     * @param $attributeId
     * @return mixed
     */
    public function getAttributeValuesById($attributeId);

    /**
     * @param $attributeId
     * @param $attributeValueId
     * @return mixed
     */
    public function getAttributeValueNamesById($attributeId, $attributeValueId);

    /**
     * @param $id
     * @return Attribute
     */
    public function getManufacturerById($id);

    /**
     * @param $name
     * @return mixed
     */
    public function getManufacturerByName($name);
}