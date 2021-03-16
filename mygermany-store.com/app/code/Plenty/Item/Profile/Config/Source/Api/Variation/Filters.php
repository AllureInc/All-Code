<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Api\Variation;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Filters
 * @package Plenty\Item\Profile\Config\Source\Api\Variation
 */
class Filters implements OptionSourceInterface
{
    /**
     * @see https://developers.plentymarkets.com/rest-doc/item_variation/details#search-variations
     */
    const PROPERTIES                        = 'properties';
    const VARIATION_PROPERTIES              = 'variationProperties';
    const VARIATION_BARCODE                 = 'variationBarcodes';
    const VARIATION_BUNDLE_COMPONENTS       = 'variationBundleComponents';
    const VARIATION_COMPONENT_BUNDLES       = 'variationComponentBundles';
    const VARIATION_SALES_PRICES            = 'variationSalesPrices';
    const MARKET_ITEM_NUMBERS               = 'marketItemNumbers';
    const VARIATION_CATEGORIES              = 'variationCategories';
    const VARIATION_CLIENTS                 = 'variationClients';
    const VARIATION_MARKETS                 = 'variationMarkets';
    const VARIATION_DEFAULT_CATEGORY        = 'variationDefaultCategory';
    const VARIATION_SUPPLIERS               = 'variationSuppliers';
    const VARIATION_WAREHOUSES              = 'variationWarehouses';
    const IMAGES                            = 'images';
    const VARIATION_ATTRIBUTE_VALUES        = 'variationAttributeValues';
    const VARIATION_SKU                     = 'variationSkus';
    const VARIATION_ADDITIONAL_SKU          = 'variationAdditionalSkus';
    const UNIT                              = 'unit';
    const PARENT                            = 'parent';
    const VARIATION_TEXTS                   = 'variationTexts';
    const ITEM                              = 'item';
    const STOCK                             = 'stock';
    const STOCK_STORAGE_LOCATION            = 'stockStorageLocations';
    const ITEM_IMAGES                       = 'itemImages';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PROPERTIES, 'label'                       => __('Properties')],
            ['value' => self::VARIATION_PROPERTIES, 'label'             => __('Variation properties')],
            ['value' => self::VARIATION_BARCODE, 'label'                => __('Variation Barcodes')],
            ['value' => self::VARIATION_BUNDLE_COMPONENTS, 'label'      => __('Variation bundle components')],
            ['value' => self::VARIATION_COMPONENT_BUNDLES, 'label'      => __('Variation component bundles')],
            ['value' => self::VARIATION_SALES_PRICES, 'label'           => __('Variation sales prices')],
            ['value' => self::MARKET_ITEM_NUMBERS, 'label'              => __('Market item numbers')],
            ['value' => self::VARIATION_CATEGORIES, 'label'             => __('Variation categories')],
            ['value' => self::VARIATION_CLIENTS, 'label'                => __('Variation clients')],
            ['value' => self::VARIATION_MARKETS, 'label'                => __('Variation markets')],
            ['value' => self::VARIATION_DEFAULT_CATEGORY, 'label'       => __('Variation default category')],
            ['value' => self::VARIATION_SUPPLIERS, 'label'              => __('Variation Suppliers')],
            ['value' => self::VARIATION_WAREHOUSES, 'label'             => __('Variation warehouses')],
            ['value' => self::IMAGES, 'label'                           => __('Variation Images')],
            ['value' => self::VARIATION_ATTRIBUTE_VALUES, 'label'       => __('Variation attribute values')],
            ['value' => self::VARIATION_SKU, 'label'                    => __('Variation SKU')],
            ['value' => self::VARIATION_ADDITIONAL_SKU, 'label'         => __('Variation additional SKU')],
            ['value' => self::UNIT, 'label'                             => __('Unit')],
            ['value' => self::PARENT, 'label'                           => __('Parent')],
            ['value' => self::VARIATION_TEXTS, 'label'                  => __('Variation Texts')],
            ['value' => self::ITEM, 'label'                             => __('Item')],
            ['value' => self::STOCK, 'label'                            => __('Stock')],
            ['value' => self::STOCK_STORAGE_LOCATION, 'label'           => __('Stock storage location')],
            ['value' => self::ITEM_IMAGES, 'label'                      => __('Item images')],
        ];
    }
}