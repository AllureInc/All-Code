<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Helper\Rest;

/**
 * Interface ProfileInterface
 * @package Plenty\Core\Api\Data
 */
interface RouteInterface
{
    const STOCK_MANAGEMENT_URL              = '/rest/stockmanagement/stock';
    const STOCK_MANAGEMENT_WAREHOUSE_URL    = '/rest/stockmanagement/warehouses';

    /**
     * @param $warehouseId
     * @return mixed
     */
    public function getListStockPerWarehouseUrl($warehouseId);

    /**
     * @param $warehouseId
     * @return mixed
     */
    public function getListStockUrl($warehouseId);

    /**
     * @param $variationId
     * @param $warehouseId
     * @return mixed
     */
    public function getListStockPerItemUrl($variationId, $warehouseId);

    /**
     * @param $warehouseId
     * @return mixed
     */
    public function getStockCorrectionByWarehouseIdUrl($warehouseId);
}
