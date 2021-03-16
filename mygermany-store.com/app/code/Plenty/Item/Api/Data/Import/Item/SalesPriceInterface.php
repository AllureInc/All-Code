<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\Collection;

/**
 * Interface SalesPriceInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface SalesPriceInterface
{
    const ENTITY_ID             = 'entity_id';
    const SALES_PRICE_ID        = 'sales_price_id';
    const ITEM_ID               = 'item_id';
    const VARIATION_ID          = 'variation_id';
    const EXTERNAL_ID           = 'external_id';
    const SKU                   = 'sku';
    const PRICE                 = 'price';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';
    const COLLECTED_AT          = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getSalesPriceId();

    public function getPrice();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @param null $priceId
     * @return Collection
     */
    public function getVariationSalesPrices(Variation $variation, $priceId = null) : Collection;
}