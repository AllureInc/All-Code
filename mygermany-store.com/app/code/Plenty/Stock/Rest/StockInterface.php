<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Rest;

use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface StockInterface
 * @package Plenty\Stock\Model\Rest
 */
interface StockInterface
{
    /**
     * @param $variationId
     * @param $warehouseId
     * @return mixed
     */
    public function getStockByItem($variationId, $warehouseId);

    /**
     * @param null $page
     * @param null $variationId
     * @param null $warehouseId
     * @param null $updatedAtFrom
     * @param null $updatedAtTo
     * @param null $itemsPerPage
     * @return Collection
     * @throws LocalizedException
     */
    public function getSearchStock(
        $page = null,
        $variationId = null,
        $warehouseId = null,
        $updatedAtFrom = null,
        $updatedAtTo = null,
        $itemsPerPage = null
    ): Collection;
}