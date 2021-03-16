<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Helper\Rest;

/**
 * Interface ProfileInterface
 * @package Plenty\Core\Api\Data
 */
interface RouteInterface
{
    const ITEM_URL                          = '/rest/items';
    const ITEM_BARCODE_URL                  = '/rest/items/barcodes';
    const VARIATION_URL                     = '/rest/items/variations';
    const VARIATION_CATEGORY_URL            = '/rest/items/variations/variation_categories';
    const CATEGORY_URL                      = '/rest/categories';
    const ITEM_SALES_PRICES_URL             = '/rest/items/sales_prices';
    const ATTRIBUTE_URL                     = '/rest/items/attributes';
    const ATTRIBUTE_VALUES_URL              = '/rest/items/attribute_values';
    const PROPERTIES_URL                    = '/rest/items/properties';
    const MANUFACTURER_URL                  = '/rest/items/manufacturers';
    const ITEM_SALES_PRICE_URL              = '/rest/items/sales_prices';

    /**
     * Returns Item Route
     * @see https://developers.plentymarkets.com/rest-doc/item/details#search-item
     *
     * @param null $itemId
     * @return mixed
     */
    public function getItemUrl($itemId = null);

    /**
     * Returns item barcode route
     * @see https://developers.plentymarkets.com/rest-doc/item_barcode/details
     *
     * @param null $barcodeId
     * @return mixed
     */
    public function getItemBarcodeUrl($barcodeId = null);

    /**
     * @param null $priceId
     * @return mixed
     */
    public function getItemSalesPriceUrl($priceId = null);

    /**
     * Returns variation route
     * @see https://developers.plentymarkets.com/rest-doc/item_variation/details#get-a-variation
     *
     * @param null $itemId
     * @param null $variationId
     * @return mixed
     */
    public function getVariationUrl($itemId = null, $variationId = null);

    /**
     * Returns a list of categories linked to a variation route
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_category/details#list-categories-linked-to-a-variation
     *
     * @param null $itemId
     * @param null $variationId
     * @param null $categoryId
     * @return mixed
     */
    public function getVariationCategoryUrl($itemId = null, $variationId = null, $categoryId = null);

    /**
     * Returns a list of default category links
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_default_category/details#list-default-category-links
     *
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function getVariationDefaultCategoryUrl($itemId, $variationId);

    /**
     * Get List texts
     * Lists the texts for an item in all available languages.
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_description/details#list-texts
     *
     * @param $itemId
     * @param $variationId
     * @param null $lang
     * @return mixed
     */
    public function getVariationDescriptionUrl($itemId, $variationId, $lang = null);

    /**
     * Get list property values linked to variation
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_property_value/details#list-property-values-linked-to-a-variation
     *
     * @param $itemId
     * @param $variationId
     * @param null $propertyId
     * @return mixed
     */
    public function getVariationPropertyValueUrl($itemId, $variationId, $propertyId = null);

    /**
     * Get Variation Bundle Url
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_bundle/details#get-a-variation-bundle
     *
     * @param $itemId
     * @param $variationId
     * @param null $bundleId
     * @return mixed
     */
    public function getVariationBundleUrl($itemId, $variationId, $bundleId = null);

    /**
     * Get Variation Market Identification Numbers
     * @see https://developers.plentymarkets.com/rest-doc/item_variationmarket_ident_number/details#list-ident-number-of-a-variation
     *
     * @param $itemId
     * @param $variationId
     * @param null $marketNumberId
     * @return mixed
     */
    public function getVariationMarketNumbersUrl($itemId, $variationId, $marketNumberId = null);

    /**
     * Get List categories by id
     * @see https://developers.plentymarkets.com/rest-doc/category_category/details#list-categories
     *
     * @param null $categoryId
     * @return mixed
     */
    public function getCategoryUrl($categoryId = null);

    /**
     * Get List sales prices
     * @see https://developers.plentymarkets.com/rest-doc/item_sales_price/details#list-sales-prices
     *
     * @return mixed
     */
    public function getItemSalesPricesUrl();

    /**
     * Get List images of an item
     * @see https://developers.plentymarkets.com/rest-doc/item_image/details#list-images-of-an-item
     *
     * @param $itemId
     */
    public function getItemImagesUrl($itemId);

    /**
     * Upload item images
     * @see https://developers.plentymarkets.com/rest-doc/item_image/details#upload-a-new-image
     *
     * @param $itemId
     * @param null $imageId
     * @return mixed
     */
    public function getItemImageUploadUrl($itemId, $imageId = null);

    /**
     * Get an attribute
     * @see https://developers.plentymarkets.com/rest-doc/item_attribute/details#get-an-attribute
     *
     * @param null $attributeId
     * @return mixed
     */
    public function getAttributeUrl($attributeId = null);

    /**
     * Get Attribute Names Url
     * @see https://developers.plentymarkets.com/rest-doc/item_attribute_name/details#get-an-attribute-name
     *
     * @param $attributeId
     * @return mixed
     */
    public function getAttributeNamesUrl($attributeId);

    /**
     * Get an attribute value
     * @see https://developers.plentymarkets.com/rest-doc/item_attribute_value/details#get-an-attribute-value
     *
     * @param $attributeId
     * @param null $valueId
     * @return mixed
     */
    public function getAttributeValueUrl($attributeId, $valueId = null);

    /**
     * Get an attribute name
     * @see https://developers.plentymarkets.com/rest-doc/item_attribute_name/details#get-an-attribute-name
     *
     * @param $valueId
     * @param null $lang
     * @return mixed
     */
    public function getAttributeValueNameUrl($valueId, $lang = null);

    /**
     * Get List properties
     * @see https://developers.plentymarkets.com/rest-doc/item_property/details#list-properties
     *
     * @param null $id
     * @return mixed
     */
    public function getPropertiesUrl($id = null);

    /**
     * Get Property Names
     * @see https://developers.plentymarkets.com/rest-doc/item_property_name/details#list-the-property-names
     *
     * @param $propertyId
     * @param null $lang
     * @return mixed
     */
    public function getPropertyNamesUrl($propertyId, $lang = null);

    /**
     * @param $propertyId
     * @param null $selectionId
     * @param null $lang
     * @return mixed
     */
    public function getPropertySelectionsUrl($propertyId, $selectionId = null, $lang = null);

    /**
     * Get property value texts
     * @see https://developers.plentymarkets.com/rest-doc/item_variation_property_value_text/details#get-property-value-texts
     *
     * @param $itemId
     * @param $variationId
     * @param $propertyId
     * @param null $lang
     * @return mixed
     */
    public function getPropertyValueTextUrl($itemId, $variationId, $propertyId, $lang = null);

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function getVariationPropertiesUrl($itemId, $variationId);

    /**
     * @param $itemId
     * @param $variationId
     * @param null $priceId
     * @return mixed
     */
    public function getVariationSalesPricesUrl($itemId, $variationId, $priceId = null);

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed
     */
    public function getVariationStockCorrectionUrl($itemId, $variationId);

    /**
     * @param null $id
     * @return mixed
     */
    public function getManufacturersUrl($id = null);
}
