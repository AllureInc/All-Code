<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface PropertyDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface PropertyDataInterface
{
    const ID                            = 'id';
    const ITEM_ID                       = 'itemId';
    const VARIATION_ID                  = 'variationId';
    const PROPERTY_ID                   = 'propertyId';
    const PROPERTY_SELECTION_ID         = 'propertySelectionId';
    const VALUE_INT                     = 'valueInt';
    const VALUE_FLOAT                   = 'valueFloat';
    const VALUE_FILE                    = 'valueFile';
    const SURCHARGE                     = 'surcharge';
    const NAMES                         = 'names';
    const PROPERTY_SELECTION            = 'propertySelection';
    const PROPERTY                      = 'property';
    const CREATED_AT                    = 'createdAt';
    const UPDATED_AT                    = 'updatedAt';
}