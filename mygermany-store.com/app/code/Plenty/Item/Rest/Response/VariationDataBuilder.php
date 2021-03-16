<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Plenty\Item\Api\Data\Import\Item\VariationInterface;

/**
 * Class VariationDataBuilder
 * @package Plenty\Item\Rest\Response
 */
class VariationDataBuilder implements VariationDataInterface
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(CollectionFactory $dataCollectionFactory)
    {
        $this->_dataCollectionFactory = $dataCollectionFactory;
    }

    /**
     * @param array $response
     * @return Collection
     * @throws \Exception
     */
    public function buildResponse(array $response): Collection
    {
        /** @var \Magento\Framework\Data\Collection $collection */
        $responseCollection = $this->_dataCollectionFactory->create();
        if (empty($response)) {
            return $responseCollection;
        }

        $responseCollection->setFlag('page', isset($response['page']) ? $response['page'] : null);
        $responseCollection->setFlag('totalsCount', isset($response['totalsCount']) ? $response['totalsCount'] : null);
        $responseCollection->setFlag('isLastPage', isset($response['isLastPage']) ? $response['isLastPage'] : null);
        $responseCollection->setFlag('lastPageNumber', isset($response['lastPageNumber']) ? $response['lastPageNumber'] : null);

        $responseData = isset($response['entries'])
            ? $response['entries']
            : [$response];
        foreach ($responseData as $item) {
            $itemObj = $this->__buildResponseData($item);
            $responseCollection->addItem($itemObj);
        }

        return $responseCollection;
    }

    /**
     * @param array $response
     * @return DataObject
     */
    private function __buildResponseData(array $response)
    {
        return new DataObject(
            [
                VariationInterface::ITEM_ID => isset($response[self::ITEM_ID])
                    ? $response[self::ITEM_ID]
                    : null,
                VariationInterface::VARIATION_ID => isset($response[self::ID])
                    ? $response[self::ID]
                    : null,
                VariationInterface::IS_MAIN => isset($response[self::IS_MAIN])
                    ? $response[self::IS_MAIN]
                    : false,
                VariationInterface::MAIN_VARIATION_ID => isset($response[self::MAIN_VARIATION_ID])
                    ? $response[self::MAIN_VARIATION_ID]
                    : null,
                VariationInterface::EXTERNAL_ID => isset($response[self::EXTERNAL_ID])
                    ? $response[self::EXTERNAL_ID]
                    : null,
                VariationInterface::SKU => isset($response[self::NUMBER])
                    ? $response[self::NUMBER]
                    : null,
                VariationInterface::NAME => isset($response[self::NAME])
                    ? $response[self::NAME]
                    : null,
                VariationInterface::IS_MAIN => isset($response[self::IS_MAIN])
                    ? $response[self::IS_MAIN]
                    : null,
                VariationInterface::IS_ACTIVE => isset($response[self::IS_ACTIVE])
                    ? $response[self::IS_ACTIVE]
                    : null,
                VariationInterface::CATEGORY_VARIATION_ID => isset($response[self::CATEGORY_VARIATION_ID])
                    ? $response[self::CATEGORY_VARIATION_ID]
                    : null,
                VariationInterface::MARKET_VARIATION_ID => isset($response[self::MARKET_VARIATION_ID])
                    ? $response[self::MARKET_VARIATION_ID]
                    : null,
                VariationInterface::CLIENT_VARIATION_ID => isset($response[self::CLIENT_VARIATION_ID])
                    ? $response[self::CLIENT_VARIATION_ID]
                    : null,
                VariationInterface::SALES_PRICE_VARIATION_ID => isset($response[self::SALES_PRICE_VARIATION_ID])
                    ? $response[self::SALES_PRICE_VARIATION_ID]
                    : null,
                VariationInterface::SUPPLIER_VARIATION_ID => isset($response[self::SUPPLIER_VARIATION_ID])
                    ? $response[self::SUPPLIER_VARIATION_ID]
                    : null,
                VariationInterface::WAREHOUSE_VARIATION_ID => isset($response[self::WAREHOUSE_VARIATION_ID])
                    ? $response[self::WAREHOUSE_VARIATION_ID]
                    : null,
                VariationInterface::PROPERTY_VARIATION_ID => isset($response[self::PROPERTY_VARIATION_ID])
                    ? $response[self::PROPERTY_VARIATION_ID]
                    : null,
                VariationInterface::POSITION => isset($response[self::POSITION])
                    ? $response[self::POSITION]
                    : null,
                VariationInterface::MODEL => isset($response[self::MODEL])
                    ? $response[self::MODEL]
                    : null,
                VariationInterface::PARENT_VARIATION_ID => isset($response[self::PARENT_VARIATION_ID])
                    ? $response[self::PARENT_VARIATION_ID]
                    : null,
                VariationInterface::PARENT_VARIATION_QTY => isset($response[self::PARENT_VARIATION_QTY])
                    ? $response[self::PARENT_VARIATION_QTY]
                    : null,
                VariationInterface::AVAILABILITY => isset($response[self::AVAILABILITY])
                    ? $response[self::AVAILABILITY]
                    : null,
                VariationInterface::FLAG_ONE => isset($response[self::FLAG_ONE])
                    ? $response[self::FLAG_ONE]
                    : null,
                VariationInterface::FLAG_TWO => isset($response[self::FLAG_TWO])
                    ? $response[self::FLAG_TWO]
                    : null,
                VariationInterface::ESTIMATED_AVAILABLE_AT => isset($response[self::ESTIMATED_AVAILABLE_AT])
                    ? $response[self::ESTIMATED_AVAILABLE_AT]
                    : null,
                VariationInterface::PURCHASE_PRICE => isset($response[self::PURCHASE_PRICE])
                    ? $response[self::PURCHASE_PRICE]
                    : null,
                VariationInterface::RELATED_UPDATED_AT => isset($response[self::RELATED_UPDATED_AT])
                    ? $response[self::RELATED_UPDATED_AT]
                    : null,
                VariationInterface::PRICE_CALCULATION_ID => isset($response[self::PRICE_CALCULATION_ID])
                    ? $response[self::PRICE_CALCULATION_ID]
                    : null,
                VariationInterface::PICKING => isset($response[self::PICKING])
                    ? $response[self::PICKING]
                    : null,
                VariationInterface::STOCK_LIMITATION => isset($response[self::STOCK_LIMITATION])
                    ? $response[self::STOCK_LIMITATION]
                    : null,
                VariationInterface::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE => isset($response[self::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE])
                    ? $response[self::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE]
                    : false,
                VariationInterface::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE => isset($response[self::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE])
                    ? $response[self::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE]
                    : false,
                VariationInterface::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE => isset($response[self::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE])
                    ? $response[self::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE]
                    : false,
                VariationInterface::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE => isset($response[self::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE])
                    ? $response[self::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE]
                    : false,
                VariationInterface::MAIN_WAREHOUSE_ID => isset($response[self::MAIN_WAREHOUSE_ID])
                    ? $response[self::MAIN_WAREHOUSE_ID]
                    : null,
                VariationInterface::MAX_ORDER_QTY => isset($response[self::MAX_ORDER_QTY])
                    ? $response[self::MAX_ORDER_QTY]
                    : null,
                VariationInterface::MIN_ORDER_QTY => isset($response[self::MIN_ORDER_QTY])
                    ? $response[self::MIN_ORDER_QTY]
                    : null,
                VariationInterface::INTERVAL_ORDER_QTY => isset($response[self::INTERVAL_ORDER_QTY])
                    ? $response[self::INTERVAL_ORDER_QTY]
                    : null,
                VariationInterface::AVAILABLE_UNTIL => isset($response[self::AVAILABLE_UNTIL])
                    ? $response[self::AVAILABLE_UNTIL]
                    : null,
                VariationInterface::RELEASED_AT => isset($response[self::RELEASED_AT])
                    ? $response[self::RELEASED_AT]
                    : null,
                VariationInterface::UNIT_COMBINATION_ID => isset($response[self::UNIT_COMBINATION_ID])
                    ? $response[self::UNIT_COMBINATION_ID]
                    : null,
                VariationInterface::WEIGHT_G => isset($response[self::WEIGHT_G])
                    ? $response[self::WEIGHT_G]
                    : null,
                VariationInterface::WEIGHT_NET_G => isset($response[self::WEIGHT_NET_G])
                    ? $response[self::WEIGHT_NET_G]
                    : null,
                VariationInterface::WIDTH_MM => isset($response[self::WIDTH_MM])
                    ? $response[self::WIDTH_MM]
                    : null,
                VariationInterface::LENGTH_MM => isset($response[self::LENGTH_MM])
                    ? $response[self::LENGTH_MM]
                    : null,
                VariationInterface::HEIGHT_MM => isset($response[self::HEIGHT_MM])
                    ? $response[self::HEIGHT_MM]
                    : null,
                VariationInterface::EXTRA_SHIPPING_CHARGES1 => isset($response[self::EXTRA_SHIPPING_CHARGES1])
                    ? $response[self::EXTRA_SHIPPING_CHARGES1]
                    : null,
                VariationInterface::EXTRA_SHIPPING_CHARGES2 => isset($response[self::EXTRA_SHIPPING_CHARGES2])
                    ? $response[self::EXTRA_SHIPPING_CHARGES2]
                    : null,
                VariationInterface::UNITS_CONTAINED => isset($response[self::UNITS_CONTAINED])
                    ? $response[self::UNITS_CONTAINED]
                    : null,
                VariationInterface::PALLET_TYPE_ID => isset($response[self::PALLET_TYPE_ID])
                    ? $response[self::PALLET_TYPE_ID]
                    : null,
                VariationInterface::PACKING_UNITS => isset($response[self::PACKING_UNITS])
                    ? $response[self::PACKING_UNITS]
                    : null,
                VariationInterface::PACKING_UNITS_TYPE_ID => isset($response[self::PACKING_UNITS_TYPE_ID])
                    ? $response[self::PACKING_UNITS_TYPE_ID]
                    : null,
                VariationInterface::TRANSPORTATION_COSTS => isset($response[self::TRANSPORTATION_COSTS])
                    ? $response[self::TRANSPORTATION_COSTS]
                    : null,
                VariationInterface::STORAGE_COSTS => isset($response[self::STORAGE_COSTS])
                    ? $response[self::STORAGE_COSTS]
                    : null,
                VariationInterface::CUSTOMS => isset($response[self::CUSTOMS])
                    ? $response[self::CUSTOMS]
                    : null,
                VariationInterface::OPERATION_COSTS => isset($response[self::OPERATION_COSTS])
                    ? $response[self::OPERATION_COSTS]
                    : null,
                VariationInterface::VAT_ID => isset($response[self::VAT_ID])
                    ? $response[self::VAT_ID]
                    : null,
                VariationInterface::BUNDLE_TYPE => isset($response[self::BUNDLE_TYPE])
                    ? $response[self::BUNDLE_TYPE]
                    : null,
                VariationInterface::AUTO_CLIENT_VISIBILITY => isset($response[self::AUTO_CLIENT_VISIBILITY])
                    ? $response[self::AUTO_CLIENT_VISIBILITY]
                    : null,
                VariationInterface::IS_HIDDEN_IN_CATEGORY_LIST => isset($response[self::IS_HIDDEN_IN_CATEGORY_LIST])
                    ? $response[self::IS_HIDDEN_IN_CATEGORY_LIST]
                    : null,
                VariationInterface::DEFAULT_SHIPPING_COSTS => isset($response[self::DEFAULT_SHIPPING_COSTS])
                    ? $response[self::DEFAULT_SHIPPING_COSTS]
                    : null,
                VariationInterface::CAN_SHOW_UNIT_PRICE => isset($response[self::CAN_SHOW_UNIT_PRICE])
                    ? $response[self::CAN_SHOW_UNIT_PRICE]
                    : null,
                VariationInterface::MOVING_AVERAGE_PRICE => isset($response[self::MOVING_AVERAGE_PRICE])
                    ? $response[self::MOVING_AVERAGE_PRICE]
                    : null,
                VariationInterface::AUTO_LIST_VISIBILITY => isset($response[self::AUTO_LIST_VISIBILITY])
                    ? $response[self::AUTO_LIST_VISIBILITY]
                    : null,
                VariationInterface::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE => isset($response[self::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE])
                    ? $response[self::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE]
                    : false,
                VariationInterface::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE => isset($response[self::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE])
                    ? $response[self::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE]
                    : false,
                VariationInterface::SINGLE_ITEM_COUNT => isset($response[self::SINGLE_ITEM_COUNT])
                    ? $response[self::SINGLE_ITEM_COUNT]
                    : null,
                VariationInterface::SALES_RANK => isset($response[self::SALES_RANK])
                    ? $response[self::SALES_RANK]
                    : null,
                VariationInterface::PROPERTIES => isset($response[self::PROPERTIES])
                    ? $response[self::PROPERTIES]
                    : null,
                VariationInterface::VARIATION_PROPERTIES => isset($response[self::VARIATION_PROPERTIES])
                    ? $response[self::VARIATION_PROPERTIES]
                    : null,
                VariationInterface::VARIATION_BARCODES => isset($response[self::VARIATION_BARCODES])
                    ? $response[self::VARIATION_BARCODES]
                    : null,
                VariationInterface::VARIATION_BUNDLE_COMPONENTS => isset($response[self::VARIATION_BUNDLE_COMPONENTS])
                    ? $response[self::VARIATION_BUNDLE_COMPONENTS]
                    : null,
                VariationInterface::VARIATION_SALES_PRICES => isset($response[self::VARIATION_SALES_PRICES])
                    ? $response[self::VARIATION_SALES_PRICES]
                    : null,
                VariationInterface::MARKET_ITEM_NUMBERS => isset($response[self::MARKET_ITEM_NUMBERS])
                    ? $response[self::MARKET_ITEM_NUMBERS]
                    : null,
                VariationInterface::VARIATION_CATEGORIES => isset($response[self::VARIATION_CATEGORIES])
                    ? $response[self::VARIATION_CATEGORIES]
                    : null,
                VariationInterface::VARIATION_CLIENTS => isset($response[self::VARIATION_CLIENTS])
                    ? $response[self::VARIATION_CLIENTS]
                    : null,
                VariationInterface::VARIATION_MARKETS => isset($response[self::VARIATION_MARKETS])
                    ? $response[self::VARIATION_MARKETS]
                    : null,
                VariationInterface::VARIATION_DEFAULT_CATEGORY => isset($response[self::VARIATION_DEFAULT_CATEGORY])
                    ? $response[self::VARIATION_DEFAULT_CATEGORY]
                    : null,
                VariationInterface::VARIATION_SUPPLIERS => isset($response[self::VARIATION_SUPPLIERS])
                    ? $response[self::VARIATION_SUPPLIERS]
                    : null,
                VariationInterface::VARIATION_WAREHOUSES => isset($response[self::VARIATION_WAREHOUSES])
                    ? $response[self::VARIATION_WAREHOUSES]
                    : null,
                VariationInterface::VARIATION_IMAGES => isset($response[self::VARIATION_IMAGES])
                    ? $response[self::VARIATION_IMAGES]
                    : null,
                VariationInterface::VARIATION_ATTRIBUTE_VALUES => isset($response[self::VARIATION_ATTRIBUTE_VALUES])
                    ? $response[self::VARIATION_ATTRIBUTE_VALUES]
                    : null,
                VariationInterface::VARIATION_SKUS => isset($response[self::VARIATION_SKUS])
                    ? $response[self::VARIATION_SKUS]
                    : null,
                VariationInterface::VARIATION_ADDITIONAL_SKUS => isset($response[self::VARIATION_ADDITIONAL_SKUS])
                    ? $response[self::VARIATION_ADDITIONAL_SKUS]
                    : null,
                VariationInterface::VARIATION_UNIT => isset($response[self::VARIATION_UNIT])
                    ? $response[self::VARIATION_UNIT]
                    : null,
                VariationInterface::VARIATION_PARENT => isset($response[self::VARIATION_PARENT])
                    ? $response[self::VARIATION_PARENT]
                    : null,
                VariationInterface::VARIATION_TEXTS => isset($response[self::VARIATION_TEXTS])
                    ? $response[self::VARIATION_TEXTS]
                    : null,
                VariationInterface::VARIATION_ITEM => isset($response[self::VARIATION_ITEM])
                    ? $response[self::VARIATION_ITEM]
                    : null,
                VariationInterface::VARIATION_STOCK => isset($response[self::VARIATION_STOCK])
                    ? $response[self::VARIATION_STOCK]
                    : null,
                VariationInterface::CREATED_AT => isset($response[self::CREATED_AT])
                    ? $response[self::CREATED_AT]
                    : null,
                VariationInterface::UPDATED_AT => isset($response[self::UPDATED_AT])
                    ? $response[self::UPDATED_AT]
                    : null
            ]
        );
    }
}