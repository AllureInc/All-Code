<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

/**
 * Interface CrosssellsInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface CrosssellsInterface
{
    const ENTITY_ID                 = 'entity_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const ITEM_CROSSSELLS_ID        = 'item_crosssells_id';
    const RELATIONSHIP              = 'relationship';
    const IS_DYNAMIC                = 'is_dynamic';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';
    const PROCESSED_AT              = 'processed_at';
}