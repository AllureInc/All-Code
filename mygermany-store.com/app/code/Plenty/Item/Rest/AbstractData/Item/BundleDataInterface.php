<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface BundleDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface BundleDataInterface
{
    const ID                        = 'id';
    const VARIATION_ID              = 'variationId';
    const COMPONENT_VARIATION_ID    = 'componentVariationId';
    const COMPONENT_QTY             = 'componentQuantity';
    const CREATED_AT                = 'createdAt';
    const UPDATED_AT                = 'lastUpdatedTimestamp';
}