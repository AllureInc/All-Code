<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

/**
 * Interface VariationInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface VariationInterface
{
    const ENTITY_ID                                             = 'entity_id';
    const ITEM_ID                                               = 'item_id';
    const VARIATION_ID                                          = 'variation_id';
    const MAIN_VARIATION_ID                                     = 'main_variation_id';
    const EXTERNAL_ID                                           = 'external_id';
    const SKU                                                   = 'sku';
    const NAME                                                  = 'name';
    const STATUS                                                = 'status';
    const IS_MAIN                                               = 'is_main';
    const IS_ACTIVE                                             = 'is_active';
    const PRODUCT_TYPE                                          = 'product_type';
    const CATEGORY_VARIATION_ID                                 = 'category_variation_id';
    const MARKET_VARIATION_ID                                   = 'market_variation_id';
    const CLIENT_VARIATION_ID                                   = 'client_variation_id';
    const SALES_PRICE_VARIATION_ID                              = 'sales_price_variation_id';
    const SUPPLIER_VARIATION_ID                                 = 'supplier_variation_id';
    const WAREHOUSE_VARIATION_ID                                = 'warehouse_variation_id';
    const PROPERTY_VARIATION_ID                                 = 'property_variation_id';
    const POSITION                                              = 'position';
    const MODEL                                                 = 'model';
    const PARENT_VARIATION_ID                                   = 'parent_variation_id';
    const PARENT_VARIATION_QTY                                  = 'parent_variation_qty';
    const AVAILABILITY                                          = 'availability';
    const FLAG_ONE                                              = 'flag_one';
    const FLAG_TWO                                              = 'flag_two';
    const ESTIMATED_AVAILABLE_AT                                = 'estimated_available_at';
    const PURCHASE_PRICE                                        = 'purchase_price';
    const RELATED_UPDATED_AT                                    = 'related_updated_at';
    const PRICE_CALCULATION_ID                                  = 'price_calculation_id';
    const PICKING                                               = 'picking';
    const STOCK_LIMITATION                                      = 'stock_limitation';
    const IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE                   = 'is_visible_if_net_stock_is_positive';
    const IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE             = 'is_invisible_if_net_stock_is_not_positive';
    const IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE                 = 'is_available_if_net_stock_is_positive';
    const IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE           = 'is_unavailable_if_net_stock_is_not_positive';
    const MAIN_WAREHOUSE_ID                                     = 'main_warehouse_id';
    const MAX_ORDER_QTY                                         = 'max_order_qty';
    const MIN_ORDER_QTY                                         = 'min_order_qty';
    const INTERVAL_ORDER_QTY                                    = 'interval_order_qty';
    const AVAILABLE_UNTIL                                       = 'available_until';
    const RELEASED_AT                                           = 'released_at';
    const UNIT_COMBINATION_ID                                   = 'unit_combination_id';
    const WEIGHT_G                                              = 'weight_g';
    const WEIGHT_NET_G                                          = 'weight_net_g';
    const WIDTH_MM                                              = 'width_mm';
    const LENGTH_MM                                             = 'length_mm';
    const HEIGHT_MM                                             = 'height_mm';
    const EXTRA_SHIPPING_CHARGES1                               = 'extra_shipping_charges1';
    const EXTRA_SHIPPING_CHARGES2                               = 'extra_shipping_charges2';
    const UNITS_CONTAINED                                       = 'units_contained';
    const PALLET_TYPE_ID                                        = 'pallet_type_id';
    const PACKING_UNITS                                         = 'packing_units';
    const PACKING_UNITS_TYPE_ID                                 = 'packing_units_type_id';
    const TRANSPORTATION_COSTS                                  = 'transportation_costs';
    const STORAGE_COSTS                                         = 'storage_costs';
    const CUSTOMS                                               = 'customs';
    const OPERATION_COSTS                                       = 'operation_costs';
    const VAT_ID                                                = 'vat_id';
    const BUNDLE_TYPE                                           = 'bundle_type';
    const AUTO_CLIENT_VISIBILITY                                = 'auto_client_visibility';
    const IS_HIDDEN_IN_CATEGORY_LIST                            = 'is_hidden_in_category_list';
    const DEFAULT_SHIPPING_COSTS                                = 'default_shipping_costs';
    const CAN_SHOW_UNIT_PRICE                                   = 'can_show_unit_price';
    const MOVING_AVERAGE_PRICE                                  = 'moving_average_price';
    const AUTO_LIST_VISIBILITY                                  = 'auto_list_visibility';
    const IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE           = 'is_visible_in_list_if_net_stock_is_positive';
    const IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE     = 'is_invisible_in_list_if_net_stock_is_not_positive';
    const SINGLE_ITEM_COUNT                                     = 'single_item_count';
    const SALES_RANK                                            = 'sales_rank';
    const PROPERTIES                                            = 'properties';
    const VARIATION_PROPERTIES                                  = 'variation_properties';
    const VARIATION_BARCODES                                    = 'variation_barcodes';
    const VARIATION_BUNDLE_COMPONENTS                           = 'variation_bundle_components';
    const VARIATION_SALES_PRICES                                = 'variation_sales_prices';
    const MARKET_ITEM_NUMBERS                                   = 'market_item_numbers';
    const VARIATION_CATEGORIES                                  = 'variation_categories';
    const VARIATION_CLIENTS                                     = 'variation_clients';
    const VARIATION_MARKETS                                     = 'variation_markets';
    const VARIATION_DEFAULT_CATEGORY                            = 'variation_default_category';
    const VARIATION_SUPPLIERS                                   = 'variation_suppliers';
    const VARIATION_WAREHOUSES                                  = 'variation_warehouses';
    const VARIATION_IMAGES                                      = 'variation_images';
    const VARIATION_ATTRIBUTE_VALUES                            = 'variation_attribute_values';
    const VARIATION_SKUS                                        = 'variation_skus';
    const VARIATION_ADDITIONAL_SKUS                             = 'variation_additional_skus';
    const VARIATION_UNIT                                        = 'variation_unit';
    const VARIATION_PARENT                                      = 'variation_parent';
    const VARIATION_TEXTS                                       = 'variation_texts';
    const VARIATION_DESCRIPTION                                 = 'variation_description';
    const VARIATION_ITEM                                        = 'variation_item';
    const VARIATION_STOCK                                       = 'variation_stock';
    const MESSAGE                                               = 'message';
    const CREATED_AT                                            = 'created_at';
    const UPDATED_AT                                            = 'updated_at';
    const COLLECTED_AT                                          = 'collected_at';
    const PROCESSED_AT                                          = 'processed_at';

    public function getItemId();

    public function getVariationId();

    public function getMainVariationId();

    public function getExternalId();

    public function getSku();

    public function getName();

    public function getStatus();

    public function getIsMain();

    public function getIsActive();

    public function getProductType();

    public function setProductType($type);

    public function getCategoryVariationId();

    public function getMarketVariationId();

    public function getClientVariationId();

    public function getSalesPriceVariationId();

    public function getSupplierVariationId();

    public function getWarehouseVariationId();

    public function getPropertyVariationId();

    public function getPosition();

    public function getModel();

    public function getParentVariationId();

    public function getParentVariationQty();

    public function getAvailability();

    public function getFlagOne();

    public function getFlagTwo();

    public function getEstimatedAvailableAt();

    public function getPurchasePrice();

    public function getRelatedUpdatedAt();

    public function getPriceCalculationId();

    public function getPicking();

    public function getStockLimitation();

    public function getIsVisibleIfNetStockIsPositive();

    public function getIsInvisibleIfNetStockIsNotPositive();

    public function getIsAvailableIfNetStockIsPositive();

    public function getIsUnavailableIfNetStockIsNotPositive();

    public function getMainWarehouseId();

    public function getMaxOrderQty();

    public function getMinOrderQty();

    public function getIntervalOrderQty();

    public function getAvailableUnit();

    public function getReleasedAt();

    public function getUnitCombinationId();

    public function getWeightG();

    public function getWeightNetG();

    public function getWidthMm();

    public function getLengthMm();

    public function getHeightMm();

    public function getExtraShippingCharge1();

    public function getExtraShippingCharge2();

    public function getUnitsContained();

    public function getPalletTypeId();

    public function getPackingUnits();

    public function getPackingUnitTypeId();

    public function getTransportationCosts();

    public function getStorageCosts();

    public function getCustoms();

    public function getOperationCosts();

    public function getVatId();

    public function getBundleType();

    public function setBundleType($type);

    public function getAutoClientVisibility();

    public function getIsHiddenInCategoryList();

    public function getDefaultShippingCosts();

    public function getCanShowUnitPrice();

    public function getMovingAveragePrice();

    public function getAutoListVisibility();

    public function getIsVisibleInListIfNetStockIsPositive();

    public function getIsInvisibleInListIfNetStockIsNotPositive();

    public function getSingleItemCount();

    public function getSalesRank();

    public function getProperties();

    public function getVariationClients();

    public function getVariationMarkets();

    public function getVariationDefaultCategory();

    public function getVariationSkus();

    public function getVariationAdditionalSkus();

    public function getVariationUnit();

    public function getMessage();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    public function getProcessedAt();

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return \Plenty\Item\Api\Data\Import\Item\VariationInterface
     */
    public function getItemVariation(\Plenty\Item\Model\Import\Item $item);

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemVariationCollection(\Plenty\Item\Model\Import\Item $item);

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemLinkedVariations(\Plenty\Item\Model\Import\Item $item);

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue\Collection
     */
    public function getVariationAttributeValues();

    /**
     * @param null $barcodeId
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Barcode\Collection
     */
    public function getVariationBarcodes($barcodeId = null);

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Bundle\Collection
     */
    public function getVariationBundleComponents();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Category\Collection
     */
    public function getVariationCategories();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\Collection
     */
    public function getVariationMarketNumbers();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection
     */
    public function getVariationProperties();

    /**
     * @param null $priceId
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\Collection
     */
    public function getVariationSalesPrices($priceId = null);

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Stock\Collection
     */
    public function getVariationStock();

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Supplier\Collection
     */
    public function getVariationSuppliers();

    /**
     * @param $lang
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Texts\Collection
     */
    public function getVariationTexts($lang = null);

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Warehouse\Collection
     */
    public function getVariationWarehouses();
}