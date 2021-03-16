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
namespace Webkul\MpAdvertisementManager\Model;

use Webkul\MpAdvertisementManager\Api\Data\PricingInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * MpAdvertisementManager Pricing Model
 *
 * @method \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing _getResource()
 * @method \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing getResource()
 */
class Pricing extends \Magento\Framework\Model\AbstractModel implements PricingInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * mobikul api cache tag
     */
    const CACHE_TAG = 'marketplace_ads_pricing';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_ads_pricing';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_ads_pricing';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing');
    }

    /**
     * Load object data
     *
     * @param  string $id
     * @param  string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route
     *
     * @return \Webkul\MpAdvertisementManager\Model\Pricing
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * Get created time
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * Set Created Time
     *
     * @param  string $createdAt
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
    }

    /**
     * Get updated time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * Set updated Time
     *
     * @param  string $updatedAt
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::CREATED_TIME, $updatedAt);
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param  int $id
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get BlockPosition
     *
     * @return int|null
     */
    public function getBlockPosition()
    {
        return parent::getData(self::BLOCK_POSITION);
    }

    /**
     * Set BlockPosition
     *
     * @param  int $blockPosition
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setBlockPosition($blockPosition)
    {
        return parent::setData(self::BLOCK_POSITION, $blockPosition);
    }

    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return parent::getData(self::PRICE);
    }

    /**
     * Set Price
     *
     * @param  float $price
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setPrice($price)
    {
        return parent::setData(self::PRICE, $price);
    }

    /**
     * Get days
     *
     * @return string|null
     */
    public function getValidFor()
    {
        return parent::getData(self::VALID_FOR);
    }

    /**
     * Set days
     *
     * @param  int $validFor
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setValidFor($validFor)
    {
        return parent::setData(self::VALID_FOR, $validFor);
    }

    /**
     * Get sortOrder
     *
     * @return int|null
     */
    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    /**
     * Set sortOrder
     *
     * @param  int $sortOrder
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setSortOrder($sortOrder)
    {
        return parent::setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return parent::getData(self::WEBSITE_ID);
    }

    /**
     * Set website id
     *
     * @param  int $websiteId
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingInterface
     */
    public function setWebsiteId($websiteId)
    {
        return parent::setData(self::WEBSITE_ID, $websiteId);
    }
}
