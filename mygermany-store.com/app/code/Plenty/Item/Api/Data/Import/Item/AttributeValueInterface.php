<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue\Collection;

/**
 * Interface AttributeValueInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface AttributeValueInterface
{
    const ENTITY_ID                             = 'entity_id';
    const ITEM_ID                               = 'item_id';
    const VARIATION_ID                          = 'variation_id';
    const EXTERNAL_ID                           = 'external_id';
    const SKU                                   = 'sku';
    const ATTRIBUTE_VALUE_SET_ID                = 'attribute_value_set_id';
    const ATTRIBUTE_ID                          = 'attribute_id';
    const VALUE_ID                              = 'value_id';
    const IS_LINKED_TO_IMAGE                    = 'is_linked_to_image';
    const ATTRIBUTE                             = 'attribute';
    const ATTRIBUTE_BACKEND_NAME                = 'attribute_backend_name';
    const ATTRIBUTE_POSITION                    = 'attribute_position';
    const VALUE_BACKEND_NAME                    = 'value_backend_name';
    const VALUE_POSITION                        = 'value_position';
    const IS_SURCHARGE_PERCENTAGE               = 'is_surcharge_percentage';
    const AMAZON_ATTRIBUTE                      = 'amazon_attribute';
    const FRUUGO_ATTRIBUTE                      = 'fruugo_attribute';
    const PIXMANIA_ATTRIBUTE                    = 'pixmania_attribute';
    const OTTO_ATTRIBUTE                        = 'otto_attribute';
    const GOOGLE_SHOPPING_ATTRIBUTE             = 'google_shopping_attribute';
    const NECKERMANN_AT_EP_ATTRIBUTE            = 'neckermann_at_ep_attribute';
    const TYPE_OF_SELECTION_IN_ONLINE_STORE     = 'type_of_selection_in_online_store';
    const LA_REDOUTE_ATTRIBUTE                  = 'la_redoute_attribute';
    const IS_GROUPABLE                          = 'is_groupable';
    const VALUE_IMAGE                           = 'value_image';
    const VALUE_COMMENT                         = 'value_comment';
    const ATTRIBUTE_VALUE                       = 'attribute_value';
    const AMAZON_VALUE                          = 'amazon_value';
    const OTTO_VALUE                            = 'otto_value';
    const NECKERMANN_AT_EP_VALUE                = 'neckermann_at_ep_value';
    const LA_REDOUTE_VALUE                      = 'la_redoute_value';
    const TRACDELIGHT_VALUE                     = 'tracdelight_value';
    const PERCENTAGE_DISTRIBUTION               = 'percentage_distribution';
    const CREATED_AT                            = 'created_at';
    const ATTRIBUTE_UPDATED_AT                  = 'attribute_updated_at';
    const VALUE_UPDATED_AT                      = 'value_updated_at';
    const COLLECTED_AT                          = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getAttributeValueSetId();

    public function getAttributeId();

    public function getValueId();

    public function getIsLinkedToImage();

    public function getAttributeBackendName();

    public function getAttributePosition();

    public function getValueBackendName();

    public function getValuePosition();

    public function getIsSurchargePercentage();

    public function getAmazonAttribute();

    public function getFruugoAttribute();

    public function getPixmaniaAttribute();

    public function getOttoAttribute();

    public function getGoogleShoppingAttribute();

    public function getNeckermannAtEpAttribute();

    public function getTypeOfSelectionInOnlineStore();

    public function getLaRedouteAttribute();

    public function getIsGroupable();

    public function getValueImage();

    public function getValueComment();

    public function getAttributeValue();

    public function getAmazonValue();

    public function getOttoValue();

    public function getNeckermannAtEpValue();

    public function getLaRedouteValue();

    public function getTracdelightvalue();

    public function getPercentageDistribution();

    public function getCreatedAt();

    public function getAttributeUpdatedAt();

    public function getValueUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationAttributeValues(Variation $variation) : Collection;

}