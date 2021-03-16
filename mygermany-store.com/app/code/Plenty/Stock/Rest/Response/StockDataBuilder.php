<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Plenty\Stock\Api\Data\Import\InventoryInterface;

/**
 * Class Stock
 * @package Plenty\Stock\Model\Rest
 */
class StockDataBuilder implements StockDataInterface
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
     * @return DataObject
     */
    protected function __buildResponseData(array $response)
    {
        return new DataObject(
            [
                InventoryInterface::ITEM_ID => isset($response[self::ITEM_ID])
                    ? $response[self::ITEM_ID]
                    : null,
                InventoryInterface::VARIATION_ID => isset($response[self::VARIATION_ID])
                    ? $response[self::VARIATION_ID]
                    : null,
                InventoryInterface::WAREHOUSE_ID => isset($response[self::WAREHOUSE_ID])
                    ? $response[self::WAREHOUSE_ID]
                    : null,
                InventoryInterface::STOCK_PHYSICAL => isset($response[self::STOCK_PHYSICAL])
                    ? $response[self::STOCK_PHYSICAL]
                    : null,
                InventoryInterface::RESERVED_STOCK => isset($response[self::RESERVED_STOCK])
                    ? $response[self::RESERVED_STOCK]
                    : null,
                InventoryInterface::RESERVED_EBAY => isset($response[self::RESERVED_EBAY])
                    ? $response[self::RESERVED_EBAY]
                    : null,
                InventoryInterface::REORDER_DELTA => isset($response[self::REORDER_DELTA])
                    ? $response[self::REORDER_DELTA]
                    : null,
                InventoryInterface::STOCK_NET => isset($response[self::STOCK_NET])
                    ? $response[self::STOCK_NET]
                    : null,
                InventoryInterface::REORDERED   => isset($response[self::REORDERED])
                    ? $response[self::REORDERED]
                    : null,
                InventoryInterface::RESERVE_BUNDLE   => isset($response[self::RESERVE_BUNDLE])
                    ? $response[self::RESERVE_BUNDLE]
                    : null,
                InventoryInterface::AVERAGE_PURCHASE_PRICE => isset($response[self::AVERAGE_PURCHASE_PRICE])
                    ? $response[self::AVERAGE_PURCHASE_PRICE]
                    : null,
                InventoryInterface::UPDATED_AT   => isset($response[self::UPDATED_AT])
                    ? $response[self::UPDATED_AT]
                    : null
            ]
        );
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

        $stockItems = isset($response['entries'])
            ? $response['entries']
            : $response;
        foreach ($stockItems as $item) {
            $itemObj = $this->__buildResponseData($item);
            $responseCollection->addItem($itemObj);
        }

        return $responseCollection;
    }
}