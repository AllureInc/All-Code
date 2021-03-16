<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\AbstractData\Order;

/**
 * Interface ItemDataInterface
 * @package Plenty\Order\Rest\AbstractData\Order
 */
interface ItemDataInterface extends \Plenty\Order\Rest\AbstractData\OrderDataInterface
{
    const ORDER_ID                  = 'orderId';
    const ITEM_VARIATION_ID         = 'itemVariationId';
    const QUANTITY                  = 'quantity';
    const ORDER_ITEM_NAME           = 'orderItemName';
    const ATTRIBUTE_VALUES          = 'attributeValues';
    const SHIPPING_PROFILE_ID       = 'shippingProfileId';
    const COUNTRY_VAT_ID            = 'countryVatId';
    const VAT_FIELD                 = 'vatField';
    const VAT_RATE                  = 'vatRate';
    const POSITION                  = 'position';
    const WAREHOUSE_ID              = 'warehouseId';
    const ORDER_PROPERTIES          = 'orderProperties';
    const REFERENCES                = 'references';
    const VARIATION                 = 'variation';
}