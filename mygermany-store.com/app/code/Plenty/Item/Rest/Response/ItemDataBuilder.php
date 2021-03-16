<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Plenty\Item\Api\Data\Import\ItemInterface;

/**
 * Class ItemDataBuilder
 * @package Plenty\Item\Rest\Response
 */
class ItemDataBuilder implements ItemDataInterface
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
                ItemInterface::ITEM_ID => isset($response[self::ID])
                    ? $response[self::ID]
                    : null,
                ItemInterface::ITEM_TYPE => isset($response[self::ITEM_TYPE])
                    ? $response[self::ITEM_TYPE]
                    : null,
                ItemInterface::MAIN_VARIATION_ID => isset($response[self::MAIN_VARIATION_ID])
                    ? $response[self::MAIN_VARIATION_ID]
                    : null,
                ItemInterface::POSITION => isset($response[self::POSITION])
                    ? $response[self::POSITION]
                    : null,
                ItemInterface::MANUFACTURER_ID => isset($response[self::MANUFACTURER_ID])
                    ? $response[self::MANUFACTURER_ID]
                    : null,
                ItemInterface::STOCK_TYPE => isset($response[self::STOCK_TYPE])
                    ? $response[self::STOCK_TYPE]
                    : null,
                ItemInterface::ADD_CMS_PAGE => isset($response[self::ADD_CMS_PAGE])
                    ? $response[self::ADD_CMS_PAGE]
                    : null,
                ItemInterface::STORE_SPECIAL => isset($response[self::STORE_SPECIAL])
                    ? $response[self::STORE_SPECIAL]
                    : null,
                ItemInterface::CONDITION => isset($response[self::CONDITION])
                    ? $response[self::CONDITION]
                    : null,
                ItemInterface::CONDITION_API => isset($response[self::CONDITION_API])
                    ? $response[self::CONDITION_API]
                    : null,
                ItemInterface::AMAZON_FEDAS => isset($response[self::AMAZON_FEDAS])
                    ? $response[self::AMAZON_FEDAS]
                    : null,
                ItemInterface::FREE1 => isset($response[self::FREE1])
                    ? $response[self::FREE1]
                    : null,
                ItemInterface::FREE2 => isset($response[self::FREE2])
                    ? $response[self::FREE2]
                    : null,
                ItemInterface::FREE3 => isset($response[self::FREE3])
                    ? $response[self::FREE3]
                    : null,
                ItemInterface::FREE4 => isset($response[self::FREE4])
                    ? $response[self::FREE4]
                    : null,
                ItemInterface::FREE5 => isset($response[self::FREE5])
                    ? $response[self::FREE5]
                    : null,
                ItemInterface::FREE6 => isset($response[self::FREE6])
                    ? $response[self::FREE5]
                    : null,
                ItemInterface::FREE6 => isset($response[self::FREE5])
                    ? $response[self::FREE5]
                    : null,
                ItemInterface::FREE7 => isset($response[self::FREE7])
                    ? $response[self::FREE7]
                    : null,
                ItemInterface::FREE8 => isset($response[self::FREE8])
                    ? $response[self::FREE8]
                    : null,
                ItemInterface::FREE9 => isset($response[self::FREE9])
                    ? $response[self::FREE9]
                    : null,
                ItemInterface::FREE10 => isset($response[self::FREE10])
                    ? $response[self::FREE10]
                    : null,
                ItemInterface::FREE11 => isset($response[self::FREE11])
                    ? $response[self::FREE11]
                    : null,
                ItemInterface::FREE12 => isset($response[self::FREE12])
                    ? $response[self::FREE12]
                    : null,
                ItemInterface::FREE13 => isset($response[self::FREE13])
                    ? $response[self::FREE13]
                    : null,
                ItemInterface::FREE14 => isset($response[self::FREE14])
                    ? $response[self::FREE14]
                    : null,
                ItemInterface::FREE15 => isset($response[self::FREE15])
                    ? $response[self::FREE15]
                    : null,
                ItemInterface::FREE16 => isset($response[self::FREE16])
                    ? $response[self::FREE16]
                    : null,
                ItemInterface::FREE17 => isset($response[self::FREE17])
                    ? $response[self::FREE17]
                    : null,
                ItemInterface::FREE18 => isset($response[self::FREE18])
                    ? $response[self::FREE18]
                    : null,
                ItemInterface::FREE19 => isset($response[self::FREE19])
                    ? $response[self::FREE19]
                    : null,
                ItemInterface::FREE20 => isset($response[self::FREE20])
                    ? $response[self::FREE20]
                    : null,
                ItemInterface::CUSTOMS_TARIFF_NO => isset($response[self::CUSTOMS_TARIFF_NUMBER])
                    ? $response[self::CUSTOMS_TARIFF_NUMBER]
                    : null,
                ItemInterface::MANUFACTURER_COUNTRY_ID => isset($response[self::MANUFACTURER_COUNTRY_ID])
                    ? $response[self::MANUFACTURER_COUNTRY_ID]
                    : null,
                ItemInterface::REVENUE_ACCOUNT => isset($response[self::REVENUE_ACCOUNT])
                    ? $response[self::REVENUE_ACCOUNT]
                    : null,
                ItemInterface::COUPON_RESTRICTION => isset($response[self::COUPON_RESTRICTION])
                    ? $response[self::COUPON_RESTRICTION]
                    : null,
                ItemInterface::FLAG_ONE => isset($response[self::FLAG_ONE])
                    ? $response[self::FLAG_ONE]
                    : null,
                ItemInterface::FLAG_TWO => isset($response[self::FLAG_TWO])
                    ? $response[self::FLAG_TWO]
                    : null,
                ItemInterface::AGE_RESTRICTION => isset($response[self::AGE_RESTRICTION])
                    ? $response[self::AGE_RESTRICTION]
                    : null,
                ItemInterface::AMAZON_PRODUCT_TYPE => isset($response[self::AMAZON_PRODUCT_TYPE])
                    ? $response[self::AMAZON_PRODUCT_TYPE]
                    : null,
                ItemInterface::EBAY_PRESET_ID => isset($response[self::EBAY_PRESET_ID])
                    ? $response[self::EBAY_PRESET_ID]
                    : null,
                ItemInterface::EBAY_CATEGORY_ID => isset($response[self::EBAY_CATEGORY_ID])
                    ? $response[self::EBAY_CATEGORY_ID]
                    : null,
                ItemInterface::EBAY_CATEGORY_ID2 => isset($response[self::EBAY_CATEGORY_ID2])
                    ? $response[self::EBAY_CATEGORY_ID2]
                    : null,
                ItemInterface::EBAY_STORE_CATEGORY => isset($response[self::EBAY_STORE_CATEGORY])
                    ? $response[self::EBAY_STORE_CATEGORY]
                    : null,
                ItemInterface::EBAY_STORE_CATEGORY2 => isset($response[self::EBAY_STORE_CATEGORY2])
                    ? $response[self::EBAY_STORE_CATEGORY2]
                    : null,
                ItemInterface::AMAZON_FBA_PLATFORM => isset($response[self::AMAZON_FBA_PLATFORM])
                    ? $response[self::AMAZON_FBA_PLATFORM]
                    : null,
                ItemInterface::FEEDBACK => isset($response[self::FEEDBACK])
                    ? $response[self::FEEDBACK]
                    : null,
                ItemInterface::MAX_ORDER_QTY => isset($response[self::MAX_ORDER_QTY])
                    ? $response[self::MAX_ORDER_QTY]
                    : null,
                ItemInterface::IS_SUBSCRIPTION => isset($response[self::IS_SUBSCRIPTION])
                    ? $response[self::IS_SUBSCRIPTION]
                    : null,
                ItemInterface::RAKUTEN_CATEGORY_ID => isset($response[self::RAKUTEN_CATEGORY_ID])
                    ? $response[self::RAKUTEN_CATEGORY_ID]
                    : null,
                ItemInterface::IS_SHIPPING_PACKAGE => isset($response[self::IS_SHIPPING_PACKAGE])
                    ? $response[self::IS_SHIPPING_PACKAGE]
                    : null,
                ItemInterface::IS_SERIAL_NUMBER => isset($response[self::IS_SERIAL_NUMBER])
                    ? $response[self::IS_SERIAL_NUMBER]
                    : null,
                ItemInterface::IS_SHIPPABLE_BY_AMAZON => isset($response[self::IS_SHIPPABLE_BY_AMAZON])
                    ? $response[self::IS_SHIPPABLE_BY_AMAZON]
                    : null,
                ItemInterface::OWNER_ID => isset($response[self::OWNER_ID])
                    ? $response[self::OWNER_ID]
                    : null,
                ItemInterface::ITEM_CROSS_SELLING => isset($response[self::ITEM_CROSS_SELLING])
                    ? $response[self::ITEM_CROSS_SELLING]
                    : null,
                ItemInterface::ITEM_VARIATIONS => isset($response[self::ITEM_VARIATIONS])
                    ? $response[self::ITEM_VARIATIONS]
                    : null,
                ItemInterface::ITEM_IMAGES => isset($response[self::ITEM_IMAGES])
                    ? $response[self::ITEM_IMAGES]
                    : null,
                ItemInterface::ITEM_SHIPPING_PROFILES => isset($response[self::ITEM_SHIPPING_PROFILES])
                    ? $response[self::ITEM_SHIPPING_PROFILES]
                    : null,
                /* @deprecated
                ItemInterface::ITEM_TEXTS => isset($response[self::ITEM_TEXTS])
                    ? $response[self::ITEM_TEXTS]
                    : null, */
                ItemInterface::CREATED_AT => isset($response[self::CREATED_AT])
                    ? $response[self::CREATED_AT]
                    : null,
                ItemInterface::UPDATED_AT => isset($response[self::UPDATED_AT])
                    ? $response[self::UPDATED_AT]
                    : null
            ]
        );
    }
}