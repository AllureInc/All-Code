<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Profile;

/**
 * Interface ProductExportInterface
 * @package Plenty\Item\Api\Data\Profile
 */
interface ProductExportInterface extends ProductInterface
{
    // Stages
    const STAGE_EXPORT_CATEGORY                 = 'export_category';
    const STAGE_EXPORT_PRODUCT                  = 'export_product';

    // Import config scope
    const ENABLE_PRODUCT_EXPORT                 = 'product_export/export_config/enable_product_export';
    const EXPORT_BEHAVIOUR                      = 'product_export/export_config/export_behaviour';
    const EXPORT_BATCH_SIZE                     = 'product_export/export_config/export_batch_size';
    const XML_PATH_IS_ACTIVE_REQUEST_LOG        = 'product_export/export_config/is_active_request_log';

    // Api config scope
    const API_BEHAVIOUR                         = 'product_export/api_config/api_behaviour';
    const API_COLLECTION_SIZE                   = 'product_export/api_config/api_collection_size';
    const API_ITEM_SEARCH_FILTERS               = 'product_export/api_config/item_search_filters';
    const API_VARIATION_SEARCH_FILTERS          = 'product_export/api_config/variation_search_filters';

    // Store config scope
    const STORE_MAPPING                         = 'product_export/store_config/store_mapping';
    const FLAG_ONE                              = 'product_export/store_config/flag_one';
    const FLAG_TWO                              = 'product_export/store_config/flag_two';

    // Tax and price config scope
    const DEFAULT_TAX_CLASS                     = 'product_export/tax_price_config/default_tax_class';
    const TAX_MAPPING                           = 'product_export/tax_price_config/tax_mapping';
    const ENABLE_PRICE_EXPORT                   = 'product_export/tax_price_config/enable_price_export';
    const PRICE_MAPPING                         = 'product_export/tax_price_config/price_mapping';
    const ENABLE_PRICE_DELETE                   = 'product_export/tax_price_config/enable_price_delete';
    const PURCHASE_PRICE_MAPPING                = 'product_export/tax_price_config/purchase_price_mapping';

    // Inventory config scope
    const ENABLE_STOCK_EXPORT                   = 'product_export/inventory_config/enable_stock_export';
    const MAIN_WAREHOUSE_ID                     = 'product_export/inventory_config/main_warehouse_id';

    // Shipping config scope
    const SHIPPING_ORDER_PICKING                = 'product_export/shipping_config/order_picking';
    const SHIPPING_MAIN_WAREHOUSE_ID            = 'product_export/shipping_config/main_warehouse_id';
    const SHIPPING_PALLET_TYPE_ID               = 'product_export/shipping_config/pallet_type_id';
    const SHIPPING_EXTRA_CHARGE1                = 'product_export/shipping_config/extra_shipping_charge1';
    const SHIPPING_EXTRA_CHARGE2                = 'product_export/shipping_config/extra_shipping_charge2';

    // Attribute config scope
    const DEFAULT_ATTRIBUTE_SET                 = 'product_export/attribute_config/default_attribute_set';
    const PRODUCT_NAME_MAPPING                  = 'product_export/attribute_config/product_name_mapping';
    const SHORT_DESCRIPTION_MAPPING             = 'product_export/attribute_config/short_description_mapping';
    const DESCRIPTION_MAPPING                   = 'product_export/attribute_config/description_mapping';
    const TECHNICAL_DATA_MAPPING                = 'product_export/attribute_config/technical_data_mapping';
    const SUPPLIER_NAME_MAPPING                 = 'product_export/attribute_config/supplier_name_mapping';
    const SUPPLIER_NUMBER_MAPPING               = 'product_export/attribute_config/supplier_number_mapping';
    const MANUFACTURER_MAPPING                  = 'product_export/attribute_config/manufacturer_mapping';
    const ENABLE_EXPORT_URL                     = 'product_export/attribute_config/enable_url_export';
    const EXPORT_URL_OPTIONS                    = 'product_export/attribute_config/export_url_options';
    const DEFAULT_WEIGHT_UNIT                   = 'product_export/attribute_config/default_weight_unit';
    const ATTRIBUTE_WIDTH_MAPPING               = 'product_export/attribute_config/dimension_width';
    const ATTRIBUTE_LENGTH_MAPPING              = 'product_export/attribute_config/dimension_length';
    const ATTRIBUTE_HEIGHT_MAPPING              = 'product_export/attribute_config/dimension_height';
    const ATTRIBUTE_DIMENSION_ADJUSTMENT = 'product_export/attribute_config/dimension_adjustment';

    // Property config scope
    const PROPERTY_MAPPING = 'product_export/property_config/property_mapping';

    // Barcode config scope
    const BARCODE_MAPPING = 'product_export/barcode_config/barcode_mapping';

    // Market number config scope
    const MARKET_NUMBER_MAPPING = 'product_export/market_number_config/market_number_mapping';

    // Category config scope
    const ENABLE_CATEGORY_EXPORT = 'product_export/category_config/is_active_category_export';
    const ROOT_CATEGORY_MAPPING = 'product_export/category_config/root_category_mapping';
    const XML_PATH_CATEGORY_FALLBACK = 'product_export/category_config/category_fallback';

    // Media config scope
    const ENABLE_MEDIA_EXPORT = 'product_export/media_config/enable_media_export';
    const MEDIA_FILTER = 'product_export/media_config/media_filter';

    // Cross-sells config scope
    const ENABLE_CROSSSELLS_EXPORT = 'product_export/crosssells_config/enable_crosssells_export';

    // custom methods
    const CONFIG_ENABLE_PRODUCT_EXPORT = 'is_active_product_export';
    const CONFIG_EXPORT_BEHAVIOUR = 'export_behaviour';
    const CONFIG_EXPORT_BATCH_SIZE = 'export_batch_size';
    const CONFIG_EXPORT_COLLECTION_FILTER = 'custom_export_collection_filter';

    /**
     * @return $this
     * @throws \Exception
     */
    public function exportProducts();

    /**
     * @return mixed
     */
    public function getIsActiveProductExport();

    /**
     * @return array|string|null
     */
    public function getExportBehaviour();

    /**
     * @return int
     */
    public function getExportBatchSize();

    /**
     * @return bool
     */
    public function getIsActiveSalesPriceExport();

    /**
     * @return bool
     */
    public function getIsActiveSalesPriceDelete();

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIsActiveExportStock($storeId = null);

    /**
     * @return string|null
     */
    public function getShippingOrderPicking();

    /**
     * @return int
     */
    public function getShippingMainWarehouseId();

    /**
     * @return int
     */
    public function getShippingPalletTypeId();


    /**
     * @return float|null
     */
    public function getShippingExtraCharge1();

    /**
     * @return float|null
     */
    public function getShippingExtraCharge2();

    /**
     * @return bool
     */
    public function getIsActiveExportUrl();

    /**
     * @return int
     */
    public function getExportUrlOption();

    /**
     * @return int|null
     */
    public function getDimensionsAdjustments();

    /**
     * @return bool
     */
    public function getIsActiveCategoryExport();

    /**
     * @return int
     * @throws \Exception
     */
    public function getFallbackCategory();

    /**
     * @return bool
     */
    public function getIsActiveExportMedia();

    /**
     * @return bool
     */
    public function getIsActiveExportCrossSells();

    /**
     * @return array
     */
    public function getProductExportSearchCriteria();

    /**
     * @var array $filter
     *
     * Column-value pairs:
     * ['entity_id' => 100]
     * or multi-dimensional array of column-value pairs:
     * ['entity_id' => ['in' => [100, 101, 102]]
     */
    public function setProductExportSearchCriteria(array $filter);

    /**
     * @return array
     */
    public function getAllowedProductExportSearchCriteria();
}
