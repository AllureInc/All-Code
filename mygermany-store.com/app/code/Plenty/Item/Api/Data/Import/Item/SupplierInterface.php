<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Supplier\Collection;

/**
 * Interface SupplierInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface SupplierInterface
{
    const ENTITY_ID                 = 'entity_id';
    const PLENTY_ENTITY_ID          = 'plenty_entity_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const SUPPLIER_ID               = 'supplier_id';
    const PURCHASE_PRICE            = 'purcahse_price';
    const MINIMUM_PURCHASE          = 'minimum_purchase';
    const ITEM_NUMBER               = 'item_number';
    const LAST_PRICE_QUERY          = 'last_price_query';
    const DELIVERY_TIME_IN_DAYS     = 'delivery_time_in_days';
    const DISCOUNT                  = 'discount';
    const IS_DISCOUNTABLE           = 'is_discountable';
    const PACKAGING_UNIT            = 'packaging_unit';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getPlentyEntityId();

    public function getSupplierId();

    public function getPurchasedPrice();

    public function getMinimumPurchase();

    public function getItemNumber();

    public function getLastPriceQuery();

    public function getDeliveryTimeInDays();

    public function getDiscount();

    public function getIsDiscountable();

    public function getPackagingUnit();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationSuppliers(Variation $variation) : Collection;
}