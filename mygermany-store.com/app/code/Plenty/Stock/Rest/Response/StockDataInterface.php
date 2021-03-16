<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface StockDataInterface
 * @package Plenty\Stock\Rest\Response
 */
interface StockDataInterface
{
    const ITEM_ID                   = 'itemId';
    const VARIATION_ID              = 'variationId';
    const WAREHOUSE_ID              = 'warehouseId';
    const STOCK_PHYSICAL            = 'stockPhysical';
    const RESERVED_STOCK            = 'reservedStock';
    const RESERVED_EBAY             = 'reservedEbay';
    const REORDER_DELTA             = 'reorderDelta';
    const STOCK_NET                 = 'stockNet';
    const REORDERED                 = 'reordered';
    const RESERVE_BUNDLE            = 'reservedBundle';
    const AVERAGE_PURCHASE_PRICE    = 'averagePurchasePrice';
    const UPDATED_AT                = 'updatedAt';

    public function buildResponse(array $response): Collection;
}