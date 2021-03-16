<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Bundle\Collection;

/**
 * Interface BundleInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface BundleInterface
{
    const ENTITY_ID                 = 'entity_id';
    const VARIATION_BUNDLE_ID       = 'variation_bundle_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const COMPONENT_SKU             = 'component_sku';
    const COMPONENT_NAME            = 'component_name';
    const COMPONENT_VARIATION_ID    = 'component_variation_id';
    const COMPONENT_QTY             = 'component_qty';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getComponentVariationId();

    public function getComponentSku();

    public function getComponentName();

    public function getComponentQty();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getBundleComponents(Variation $variation) : Collection;

}