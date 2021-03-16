<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Profile;

/**
 * Interface ProductImportInterface
 * @package Plenty\Item\Api\Data\Profile
 */
interface ProductImportInterface extends ProductInterface
{
    // Stages
    const STAGE_IMPORT_CATEGORY                         = 'import_category';
    const STAGE_IMPORT_PRODUCT                          = 'import_product';

    // Import config scope
    const XML_PATH_ENABLE_PRODUCT_IMPORT                = 'product_import/import_config/enable_product_import';
    const XML_PATH_IMPORT_BEHAVIOUR                     = 'product_import/import_config/import_behaviour';
    const XML_PATH_IMPORT_BATCH_SIZE                    = 'product_import/import_config/import_batch_size';
    const XML_PATH_REINDEX_AFTER                        = 'product_import/import_config/reindex_after';
    const XML_PATH_IS_ACTIVE_REQUEST_LOG                = 'product_import/import_config/is_active_request_log';

    // Api config scope
    const XML_PATH_API_BEHAVIOUR                        = 'product_import/api_config/api_behaviour';
    const XML_PATH_API_COLLECTION_SIZE                  = 'product_import/api_config/api_collection_size';
    const XML_PATH_API_ITEM_SEARCH_FILTERS              = 'product_import/api_config/item_search_filters';
    const XML_PATH_API_VARIATION_SEARCH_FILTERS         = 'product_import/api_config/variation_search_filters';

    // Store config scope
    const XML_PATH_STORE_MAPPING                        = 'product_import/store_config/store_mapping';
    const XML_PATH_FLAG_ONE                             = 'product_import/store_config/flag_one';
    const XML_PATH_FLAG_TWO                             = 'product_import/store_config/flag_two';

    // Tax and price config scope
    const XML_PATH_DEFAULT_TAX_CLASS                    = 'product_import/tax_price_config/default_tax_class';
    const XML_PATH_TAX_MAPPING                          = 'product_import/tax_price_config/tax_mapping';
    const XML_PATH_PRICE_MAPPING                        = 'product_import/tax_price_config/price_mapping';
    const XML_PATH_PURCHASE_PRICE_MAPPING               = 'product_import/tax_price_config/purchase_price_mapping';

    // Inventory config scope
    const XML_PATH_IS_ACTIVE_STOCK_IMPORT               = 'product_import/inventory_config/enable_stock_import';
    const XML_PATH_MAIN_WAREHOUSE_ID                    = 'product_import/inventory_config/main_warehouse_id';

    // Attribute config scope
    const XML_PATH_DEFAULT_ATTRIBUTE_SET                = 'product_import/attribute_config/default_attribute_set';
    const XML_PATH_ENABLE_ATTRIBUTE_RESTRICTION         = 'product_import/attribute_config/enable_attribute_restriction';
    const XML_PATH_ALLOWED_ATTRIBUTES                   = 'product_import/attribute_config/allowed_attributes';
    const XML_PATH_PRODUCT_NAME_MAPPING                 = 'product_import/attribute_config/product_name_mapping';
    const XML_PATH_SHORT_DESCRIPTION_MAPPING            = 'product_import/attribute_config/short_description_mapping';
    const XML_PATH_DESCRIPTION_MAPPING                  = 'product_import/attribute_config/description_mapping';
    const XML_PATH_TECHNICAL_DATA_MAPPING               = 'product_import/attribute_config/technical_data_mapping';
    const XML_PATH_SUPPLIER_NAME_MAPPING                = 'product_import/attribute_config/supplier_name_mapping';
    const XML_PATH_SUPPLIER_NUMBER_MAPPING              = 'product_import/attribute_config/supplier_number_mapping';
    const XML_PATH_MANUFACTURER_MAPPING                 = 'product_import/attribute_config/manufacturer_mapping';
    const XML_PATH_ENABLE_IMPORT_URL                    = 'product_import/attribute_config/enable_import_url';
    const XML_PATH_IMPORT_URL_OPTIONS                   = 'product_import/attribute_config/import_url_options';
    const XML_PATH_DEFAULT_WEIGHT_UNIT                  = 'product_import/attribute_config/default_weight_unit';

    // Property config scope
    const XML_PATH_PROPERTY_MAPPING                     = 'product_import/property_config/property_mapping';

    // Barcode config scope
    const XML_PATH_BARCODE_MAPPING                      = 'product_import/barcode_config/barcode_mapping';

    // Market number config scope
    const XML_PATH_MARKET_NUMBER_MAPPING                = 'product_import/market_number_config/market_number_mapping';

    // Category config scope
    const XML_PATH_IS_ACTIVE_CATEGORY_IMPORT = 'product_import/category_config/is_active_category_import';
    const XML_PATH_CATEGORY_FALLBACK = 'product_import/category_config/category_fallback';
    const XML_PATH_ROOT_CATEGORY_MAPPING = 'product_import/category_config/root_category_mapping';

    // Media config scope
    const XML_PATH_ENABLE_MEDIA_IMPORT                  = 'product_import/media_config/enable_media_import';
    const XML_PATH_ENABLE_DOWNLOAD_MEDIA                = 'product_import/media_config/enable_download_media';
    const XML_PATH_MEDIA_FILTER                         = 'product_import/media_config/media_filter';

    // Cross-sells config scope
    const XML_PATH_ENABLE_CROSSSELLS_IMPORT             = 'product_import/crosssells_config/enable_crosssells_import';

    // custom methods
    const CONFIG_IS_ACTIVE_PRODUCT_IMPORT = 'is_active_product_import';
    const CONFIG_IMPORT_BEHAVIOUR = 'import_behaviour';
    const CONFIG_IMPORT_BATCH_SIZE = 'import_batch_size';
    const CONFIG_IS_ACTIVE_REINDEX_AFTER = 'is_active_reindex_after';
    const CONFIG_IS_ACTIVE_CATEGORY_IMPORT = 'is_active_category_import';
    const CONFIG_IMPORT_SEARCH_CRITERIA = 'import_search_filter';

    /**
     * @return $this
     * @throws \Exception
     */
    public function importCategories();

    /**
     * @return $this
     * @throws \Exception
     */
    public function importProducts();

    /**
     * @return mixed
     */
    public function getIsActiveProductImport();

    /**
     * @return array|string|null
     */
    public function getImportBehaviour();

    /**
     * @return int
     */
    public function getImportBatchSize();

    /**
     * @return bool
     */
    public function getIsActiveReindexAfter();

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIsActiveStockImport($storeId = null);

    /**
     * @return bool
     */
    public function getIsActiveAttributeRestriction();

    /**
     * @return array
     */
    public function getAllowedAttributes();

    /**
     * @return bool
     */
    public function getIsActiveImportUrl();

    /**
     * @return int
     */
    public function getImportUrlOption();

    /**
     * @return bool
     */
    public function getIsActiveCategoryImport();

    /**
     * @param $bool
     * @return mixed
     */
    public function setIsActiveCategoryImport($bool);

    /**
     * @return int
     */
    public function getCategoryFallback();

    /**
     * @return bool
     */
    public function getIsActiveMediaImport();

    /**
     * @return bool
     */
    public function getIsActiveDownloadMedia();

    /**
     * @return bool
     */
    public function getIsActiveCrossSellsImport();

    /**
     * @return array
     */
    public function getProductImportSearchCriteria();

    /**
     * @var array $filter
     *
     * Column-value pairs:
     * ['entity_id' => 100]
     * or multi-dimensional array of column-value pairs:
     * ['entity_id' => ['in' => [100, 101, 102]]
     *
     * @return $this
     */
    public function setProductImportSearchCriteria(array $filter);

    /**
     * @return array
     */
    public function getProductImportSearchCriteriaAllowedFields();


}
