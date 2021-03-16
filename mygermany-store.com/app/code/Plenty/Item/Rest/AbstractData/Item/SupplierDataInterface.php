<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface SupplierDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface SupplierDataInterface
{
    const ID                        = 'id';
    const VARIATION_ID              = 'variationId';
    const SUPPLIER_ID               = 'supplierId';
    const PURCHASE_PRICE            = 'purchasePrice';
    const MINIMUM_PURCHASE          = 'minimumPurchase';
    const ITEM_NUMBER               = 'itemNumber';
    const LAST_PRICE_QUERY          = 'lastPriceQuery';
    const DELIVERY_TIME_IN_DAYS     = 'deliveryTimeInDays';
    const DISCOUNT                  = 'discount';
    const IS_DISCOUNTABLE           = 'isDiscountable';
    const PACKAGING_UNIT            = 'packagingUnit';
    const CREATED_AT                = 'createdAt';
    const UPDATED_AT                = 'lastUpdateTimestamp';
}