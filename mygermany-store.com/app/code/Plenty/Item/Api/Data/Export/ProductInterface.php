<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Export;

/**
 * Interface ProductInterface
 * @package Plenty\Item\Api\Data\Export
 */
interface ProductInterface
{
    const ENTITY_ID = 'entity_id';
    const PROFILE_ID = 'profile_id';
    const PRODUCT_ID = 'product_id';
    const SKU = 'sku';
    const NAME = 'name';
    const ITEM_ID = 'item_id';
    const VARIATION_ID = 'variation_id';
    const MAIN_VARIATION_ID = 'main_variation_id';
    const TYPE = 'type';
    const PRODUCT_TYPE = 'product_type';
    const ATTRIBUTE_SET = 'attribute_set';
    const VISIBILITY = 'visibility';
    const STATUS = 'status';
    const ENTRIES = 'entries';
    const MESSAGE = 'message';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const COLLECTED_AT = 'collected_at';
    const PROCESSED_AT = 'processed_at';
    const PROFILE_ENTITY = 'profile_entity';

    const PLENTY_ITEM_ID = 'plenty_item_id';
    const PLENTY_VARIATION_ID = 'plenty_variation_id';
    const IS_MAIN_PRODUCT = 'is_main_product';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string|null
     */
    public function getSku();

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int|null
     */
    public function getItemId();

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * @return int|null
     */
    public function getVariationId();

    /**
     * @param $variationId
     * @return $this
     */
    public function setVariationId($variationId);

    /**
     * @return int|null
     */
    public function getMainVariationId();

    /**
     * @param int $mainVariationId
     * @return $this
     */
    public function setMainVariationId($mainVariationId);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string|null
     */
    public function getProductType();

    /**
     * @param $productType
     * @return $this
     */
    public function setProductType($productType);

    /**
     * @return string|null
     */
    public function getAttributeSet();

    /**
     * @param $attributeSet
     * @return $this
     */
    public function setAttributeSet($attributeSet);

    /**
     * @return string|null
     */
    public function getVisibility();

    /**
     * @param $visibility
     * @return $this
     */
    public function setVisibility($visibility);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return array
     */
    public function getEntries();

    /**
     * @param array $entries
     * @return $this
     */
    public function setEntries(array $entries);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCreatedAt($dateTime);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setUpdatedAt($dateTime);

    /**
     * @return string|null
     */
    public function getProcessedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setProcessedAt($dateTime);

    /**
     * @param int $profileId
     * @param array $productIds
     * @return $this
     * @throws \Exception
     */
    public function addProductsToExport($profileId, array $productIds);
}