<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\Import\Item\Variation;
use Plenty\Item\Model\ResourceModel\Import\Item\Texts\Collection;

/**
 * Interface TextsInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface TextsInterface
{
    const ENTITY_ID             = 'entity_id';
    const PLENTY_ENTITY_ID      = 'plenty_entity_id';
    const ITEM_ID               = 'item_id';
    const VARIATION_ID          = 'variation_id';
    const EXTERNAL_ID           = 'external_id';
    const SKU                   = 'sku';
    const LANG                  = 'lang';
    const NAME                  = 'name';
    const NAME2                 = 'name2';
    const NAME3                 = 'name3';
    const SHORT_DESCRIPTION     = 'short_description';
    const DESCRIPTION           = 'description';
    const TECHNICAL_DATA        = 'technical_data';
    const URL_PATH              = 'url_path';
    const META_DESCRIPTION      = 'meta_description';
    const META_KEYWORDS         = 'meta_keywords';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';
    const COLLECTED_AT          = 'collected_at';
    const PROCESSED_AT          = 'processed_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getPlentyEntityId();

    public function getLang();

    public function getName();

    public function getName2();

    public function getName3();

    public function getShortDescription();

    public function getDescription();

    public function getTechnicalData();

    public function getUrlPath();

    public function getMetaDescription();

    public function getMetaKeywords();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param Variation $variation
     * @param null $lang
     * @return Collection
     */
    public function getVariationTexts(Variation $variation, $lang = null) : Collection;
}