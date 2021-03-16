<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Config;

use Plenty\Core\Api\Data\Config\SourceInterface as CoreSourceInterface;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection;

/**
 * Interface SourceInterface
 * @package Plenty\Item\Api\Data\Config
 */
interface SourceInterface extends CoreSourceInterface
{
    const CONFIG_SOURCE_ITEM_SALES_PRICE        = 'item_sales_price';
    const CONFIG_SOURCE_ITEM_BARCODE            = 'item_barcode';
    // const CONFIG_SOURCE_STOCK_WAREHOUSE         = 'stock_warehouse';

    /**
     * @param null $barcodeId
     * @param null $updatedAt
     * @return mixed
     */
    public function collectItemBarcodeConfigs($barcodeId = null, $updatedAt = null);

    /**
     * @param null $priceId
     * @return mixed
     */
    public function collectItemSalesPrices($priceId = null);

    /**
     * @return Collection
     */
    public function getBarcodeCollection(): Collection;

    /**
     * @return Collection
     */
    public function getSalesPriceCollection(): Collection;
}