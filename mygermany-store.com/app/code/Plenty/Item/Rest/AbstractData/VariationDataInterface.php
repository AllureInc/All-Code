<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData;

/**
 * Interface VariationDataInterface
 * @package Plenty\Item\Rest\AbstractData
 */
interface VariationDataInterface
{
    const ID                                                    = 'id';
    const IS_MAIN                                               = 'isMain';
    const MAIN_VARIATION_ID                                     = 'mainVariationId';
    const VARIATION_ID                                          = 'variationId';
    const ITEM_ID                                               = 'itemId';
    const CATEGORY_VARIATION_ID                                 = 'categoryVariationId';
    const MARKET_VARIATION_ID                                   = 'marketVariationId';
    const CLIENT_VARIATION_ID                                   = 'clientVariationId';
    const SALES_PRICE_VARIATION_ID                              = 'salesPriceVariationId';
    const SUPPLIER_VARIATION_ID                                 = 'supplierVariationId';
    const WAREHOUSE_VARIATION_ID                                = 'warehouseVariationId';
    const PROPERTY_VARIATION_ID                                 = 'propertyVariationId';
    const POSITION                                              = 'position';
    const IS_ACTIVE                                             = 'isActive';
    const NUMBER                                                = 'number';
    const MODEL                                                 = 'model';
    const EXTERNAL_ID                                           = 'externalId';
    const PARENT_VARIATION_ID                                   = 'parentVariationId';
    const PARENT_VARIATION_QTY                                  = 'parentVariationQuantity';
    const AVAILABILITY                                          = 'availability';
    const FLAG_ONE                                              = 'flagOne';
    const FLAG_TWO                                              = 'flagTwo';
    const ESTIMATED_AVAILABLE_AT                                = 'estimatedAvailableAt';
    const PURCHASE_PRICE                                        = 'purchasePrice';
    const CREATED_AT                                            = 'createdAt';
    const UPDATED_AT                                            = 'updatedAt';
    const RELATED_UPDATED_AT                                    = 'relatedUpdatedAt';
    const PRICE_CALCULATION_ID                                  = 'priceCalculationId';
    const PICKING                                               = 'picking';
    const STOCK_LIMITATION                                      = 'stockLimitation';
    const IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE                   = 'isVisibleIfNetStockIsPositive';
    const IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE             = 'isInvisibleIfNetStockIsNotPositive';
    const IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE                 = 'isAvailableIfNetStockIsPositive';
    const IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE           = 'isUnavailableIfNetStockIsNotPositive';
    const MAIN_WAREHOUSE_ID                                     = 'mainWarehouseId';
    const MAX_ORDER_QTY                                         = 'maximumOrderQuantity';
    const MIN_ORDER_QTY                                         = 'minimumOrderQuantity';
    const INTERVAL_ORDER_QTY                                    = 'intervalOrderQuantity';
    const AVAILABLE_UNTIL                                       = 'availableUntil';
    const RELEASED_AT                                           = 'releasedAt';
    const UNIT_COMBINATION_ID                                   = 'unitCombinationId';
    const NAME                                                  = 'name';
    const WEIGHT_G                                              = 'weightG';
    const WEIGHT_NET_G                                          = 'weightNetG';
    const WIDTH_MM                                              = 'widthMM';
    const LENGTH_MM                                             = 'lengthMM';
    const HEIGHT_MM                                             = 'heightMM';
    const EXTRA_SHIPPING_CHARGES1                               = 'extraShippingCharge1';
    const EXTRA_SHIPPING_CHARGES2                               = 'extraShippingCharge2';
    const UNITS_CONTAINED                                       = 'unitsContained';
    const PALLET_TYPE_ID                                        = 'palletTypeId';
    const PACKING_UNITS                                         = 'packingUnits';
    const PACKING_UNITS_TYPE_ID                                 = 'packingUnitTypeId';
    const TRANSPORTATION_COSTS                                  = 'transportationCosts';
    const STORAGE_COSTS                                         = 'storageCosts';
    const CUSTOMS                                               = 'customs';
    const OPERATION_COSTS                                       = 'operatingCosts';
    const VAT_ID                                                = 'vatId';
    const BUNDLE_TYPE                                           = 'bundleType';
    const AUTO_CLIENT_VISIBILITY                                = 'automaticClientVisibility';
    const IS_HIDDEN_IN_CATEGORY_LIST                            = 'isHiddenInCategoryList';
    const DEFAULT_SHIPPING_COSTS                                = 'defaultShippingCosts';
    const CAN_SHOW_UNIT_PRICE                                   = 'mayShowUnitPrice';
    const MOVING_AVERAGE_PRICE                                  = 'movingAveragePrice';
    const AUTO_LIST_VISIBILITY                                  = 'automaticListVisibility';
    const IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE           = 'isVisibleInListIfNetStockIsPositive';
    const IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE     = 'isInvisibleInListIfNetStockIsNotPositive';
    const SINGLE_ITEM_COUNT                                     = 'singleItemCount';
    const SALES_RANK                                            = 'sales_rank';
    const PROPERTIES                                            = 'properties';
    const VARIATION_PROPERTIES                                  = 'variationProperties';
    const VARIATION_BARCODES                                    = 'variationBarcodes';
    const VARIATION_BUNDLE_COMPONENTS                           = 'variationBundleComponents';
    const VARIATION_SALES_PRICES                                = 'variationSalesPrices';
    const MARKET_ITEM_NUMBERS                                   = 'marketItemNumbers';
    const VARIATION_CATEGORIES                                  = 'variationCategories';
    const VARIATION_CLIENTS                                     = 'variationClients';
    const VARIATION_MARKETS                                     = 'variationMarkets';
    const VARIATION_DEFAULT_CATEGORY                            = 'variationDefaultCategory';
    const VARIATION_SUPPLIERS                                   = 'variationSuppliers';
    const VARIATION_WAREHOUSES                                  = 'variationWarehouses';
    const VARIATION_IMAGES                                      = 'images';
    const VARIATION_ATTRIBUTE_VALUES                            = 'variationAttributeValues';
    const VARIATION_SKUS                                        = 'variationSkus';
    const VARIATION_ADDITIONAL_SKUS                             = 'variationAdditionalSkus';
    const VARIATION_UNIT                                        = 'unit';
    const VARIATION_PARENT                                      = 'parent';
    const VARIATION_TEXTS                                       = 'variationTexts';
    const VARIATION_ITEM                                        = 'item';
    const VARIATION_STOCK                                       = 'stock';
}