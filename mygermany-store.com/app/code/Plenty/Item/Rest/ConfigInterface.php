<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

/**
 * Interface ItemInterface
 * @package Plenty\Item\Rest
 */
interface ConfigInterface
{
    /**
     * @param int $page
     * @param null $barcodeId
     * @param null $updatedAt
     * @return mixed
     */
    public function getSearchItemBarcodes(
        $page = 1,
        $barcodeId = null,
        $updatedAt = null
    );

    /**
     * @param int $page
     * @param null $priceId
     * @param null $updatedAt
     * @return mixed
     */
    public function getSearchItemSalesPrices(
        $page = 1,
        $priceId = null,
        $updatedAt = null
    );

}