<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Api\Data;

/**
 * MpAdvertisementManager Pricing interface.
 *
 * @api
 */
interface PricingInterface
{

    const ENTITY_ID = 'id';

    const BLOCK_POSITION = 'block_position';

    const PRICE = 'price';

    const VALID_FOR = 'valid_for';

    const SORT_ORDER = 'sort_order';

    const WEBSITE_ID = 'website_id';
    
    const CREATED_AT = 'created_at';
    
    const UPDATED_AT = 'updated_at';



    /**#@-*/

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param  string $id
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setId($id);

    /**
     * Get BlockPosition
     *
     * @return int|null
     */
    public function getBlockPosition();

    /**
     * Set BlockPosition
     *
     * @param  int $blockPosition
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setBlockPosition($blockPosition);

    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set Price
     *
     * @param  float $price
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setPrice($price);

    /**
     * Get days
     *
     * @return string|null
     */
    public function getValidFor();

    /**
     * Set days
     *
     * @param  int $validFor
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setValidFor($validFor);

    /**
     * Get sortOrder
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set sortOrder
     *
     * @param  int $sortOrder
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param  int $websiteId
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get created date.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created date.
     *
     * @param string $createdAt
     *
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated date.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated date.
     *
     * @param string $updatedAt
     *
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setUpdatedAt($updatedAt);
}
