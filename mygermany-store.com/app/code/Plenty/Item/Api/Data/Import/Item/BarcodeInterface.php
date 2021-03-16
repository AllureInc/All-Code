<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Barcode\Collection;

/**
 * Interface BarcodeInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface BarcodeInterface
{
    const ENTITY_ID             = 'entity_id';
    const BARCODE_ID            = 'barcode_id';
    const ITEM_ID               = 'item_id';
    const VARIATION_ID          = 'variation_id';
    const EXTERNAL_ID           = 'external_id';
    const SKU                   = 'sku';
    const CODE                  = 'code';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';
    const COLLECTED_AT          = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getCode();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param null $barcodeId
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationBarcodes(Variation $variation, $barcodeId = null) : Collection;
}