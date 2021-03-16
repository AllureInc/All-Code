<?php
/**
 * Created by PhpStorm.
 * User: theexten
 * Date: 2019-01-30
 * Time: 12:47
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\Collection;

/**
 * Interface MarketNumberInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface MarketNumberInterface
{
    const ENTITY_ID             = 'entity_id';
    const PLENTY_ENTITY_ID      = 'plenty_entity_id';
    const ITEM_ID               = 'item_id';
    const VARIATION_ID          = 'variation_id';
    const EXTERNAL_ID           = 'external_id';
    const SKU                   = 'sku';
    const CODE                  = 'code';
    const COUNTRY_ID            = 'country_id';
    const TYPE                  = 'type';
    const POSITION              = 'position';
    const VALUE                 = 'value';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';
    const COLLECTED_AT          = 'collected_at';

    public function getPlentyEntityId() : int;

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getCode();

    public function getCountryId();

    public function getType();

    public function getPosition();

    public function getValue();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationMarketNumbers(Variation $variation) : Collection;
}