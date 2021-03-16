<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface AttributeValueDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface AttributeValueDataInterface
{
    const ATTRIBUTE_VALUE_SET_ID                = 'attributeValueSetId';
    const ATTRIBUTE_ID                          = 'attributeId';
    const VALUE_ID                              = 'valueId';
    const IS_LINKED_TO_IMAGE                    = 'isLinkableToImage';
    const ATTRIBUTE                             = 'attribute';
    const ID                                    = 'id';
    const BACKEND_NAME                          = 'backendName';
    const POSITION                              = 'position';
    const IS_SURCHARGE_PERCENTAGE               = 'isSurchargePercental';
    const AMAZON_ATTRIBUTE                      = 'amazonAttribute';
    const FRUUGO_ATTRIBUTE                      = 'fruugoAttribute';
    const PIXMANIA_ATTRIBUTE                    = 'pixmaniaAttribute';
    const OTTO_ATTRIBUTE                        = 'ottoAttribute';
    const GOOGLE_SHOPPING_ATTRIBUTE             = 'googleShoppingAttribute';
    const NECKERMANN_AT_EP_ATTRIBUTE            = 'neckermannAtEpAttribute';
    const TYPE_OF_SELECTION_IN_ONLINE_STORE     = 'typeOfSelectionInOnlineStore';
    const LA_REDOUTE_ATTRIBUTE                  = 'laRedouteAttribute';
    const IS_GROUPABLE                          = 'isGroupable';
    const ATTRIBUTE_VALUE                       = 'attributeValue';
    const IMAGE                                 = 'image';
    const COMMENT                               = 'comment';
    const AMAZON_VALUE                          = 'amazonValue';
    const OTTO_VALUE                            = 'ottoValue';
    const NECKERMANN_AT_EP_VALUE                = 'neckermannAtEpValue';
    const LA_REDOUTE_VALUE                      = 'laRedouteValue';
    const TRACDELIGHT_VALUE                     = 'tracdelightValue';
    const PERCENTAGE_DISTRIBUTION               = 'percentageDistribution';
    const CREATED_AT                            = 'createdAt';
    const UPDATED_AT                            = 'updatedAt';
}