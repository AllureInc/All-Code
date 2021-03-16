<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface CrossSellsDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface CrossSellsDataInterface
{
    // ITEM CROSS-SELLS
    const CROSS_ITEM_ID         = 'crossItemId';
    const RELATIONSHIP          = 'relationship';
    const IS_DYNAMIC            = 'isDynamic';
    const CREATED_AT            = 'created_timestamp';
    const UPDATED_AT            = 'last_update_timestamp';
}