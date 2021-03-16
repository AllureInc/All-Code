<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface CategoryDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface CategoryDataInterface
{
    const VARIATION_ID              = 'variationId';
    const CATEGORY_ID               = 'categoryId';
    const POSITION                  = 'position';
    const IS_NECKERMANN_PRIMARY     = 'isNeckermannPrimary';
    const CREATED_AT                = 'createdAt';
    const UPDATE_AT                 = 'updatedAt';
}