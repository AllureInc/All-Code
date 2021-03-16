<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Setup;

/**
 * Interface SchemaInterface
 * @package Plenty\Item\Setup
 */
interface SchemaInterface
{
    const ITEM_EXPORT_CATEGORY                  = 'plenty_item_export_category';
    const ITEM_EXPORT_PRODUCT                   = 'plenty_item_export_product';
    const ITEM_IMPORT_ATTRIBUTE                 = 'plenty_item_import_attribute';
    const ITEM_IMPORT_CATEGORY                  = 'plenty_item_import_category';
    const ITEM_IMPORT_ITEM                      = 'plenty_item_import_item';
    const ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE      = 'plenty_item_import_item_attribute_value';
    const ITEM_IMPORT_ITEM_BARCODE              = 'plenty_item_import_item_barcode';
    const ITEM_IMPORT_ITEM_BUNDLE               = 'plenty_item_import_item_bundle';
    const ITEM_IMPORT_ITEM_CATEGORY             = 'plenty_item_import_item_category';
    const ITEM_IMPORT_ITEM_CROSSSELLS           = 'plenty_item_import_item_crosssells';
    const ITEM_IMPORT_ITEM_MEDIA                = 'plenty_item_import_item_media';
    const ITEM_IMPORT_ITEM_MARKET_NUMBER        = 'plenty_item_import_item_market_number';
    const ITEM_IMPORT_ITEM_PROPERTY             = 'plenty_item_import_item_property';
    const ITEM_IMPORT_ITEM_SALES_PRICE          = 'plenty_item_import_item_sales_price';
    const ITEM_IMPORT_ITEM_SHIPPING_PROFILE     = 'plenty_item_import_item_shipping_profile';
    const ITEM_IMPORT_ITEM_STOCK                = 'plenty_item_import_item_stock';
    const ITEM_IMPORT_ITEM_SUPPLIER             = 'plenty_item_import_item_supplier';
    const ITEM_IMPORT_ITEM_TEXTS                = 'plenty_item_import_item_texts';
    const ITEM_IMPORT_ITEM_WAREHOUSE            = 'plenty_item_import_item_warehouse';
    const ITEM_IMPORT_ITEM_VARIATION            = 'plenty_item_import_item_variation';
}