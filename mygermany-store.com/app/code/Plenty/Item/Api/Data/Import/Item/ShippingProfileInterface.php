<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\ResourceModel\Import\Item\ShippingProfile\Collection;
/**
 * Interface ShippingProfileInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface ShippingProfileInterface
{
    const ENTITY_ID                     = 'entity_id';
    const ITEM_ID                       = 'item_id';
    const VARIATION_ID                  = 'variation_id';
    const EXTERNAL_ID                   = 'external_id';
    const SKU                           = 'sku';
    const PLENTY_ENTITY_ID              = 'plenty_entity_id';
    const PROFILE_ID                    = 'profile_id';
    const CREATED_AT                    = 'created_at';
    const UPDATED_AT                    = 'updated_at';
    const COLLECTED_AT                  = 'collected_at';
    const PROCESSED_AT                  = 'processed_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getPlentyEntityId();

    public function getProfileId();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return Collection
     */
    public function getItemShippingProfiles(\Plenty\Item\Model\Import\Item $item) : Collection;
}