<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection;
/**
 * Interface PropertyInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface PropertyInterface
{
    const ENTITY_ID                 = 'entity_id';
    const ITEM_ID                   = 'item_id';
    const VARIATION_ID              = 'variation_id';
    const EXTERNAL_ID               = 'external_id';
    const SKU                       = 'sku';
    const PLENTY_ENTITY_ID          = 'plenty_entity_id';
    const PROPERTY_ID               = 'property_id';
    const PROPERTY_SELECTION_ID     = 'property_selection_id';
    const NAMES                     = 'names';
    const PROPERTY_SELECTION        = 'property_selection';
    const PROPERTY                  = 'property';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const COLLECTED_AT              = 'collected_at';
    const PROCESSED_AT              = 'processed_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getPlentyEntityId();

    public function getPropertyId();

    public function getPropertySelectionId();

    public function getNames() : array;

    public function getPropertySelections() : array;

    public function getProperty() : array;

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationProperties(Variation $variation) : Collection;

}