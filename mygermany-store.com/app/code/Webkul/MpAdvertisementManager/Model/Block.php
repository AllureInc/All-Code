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

use Webkul\MpAdvertisementManager\Api\Data\BlockInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * MpAdvertisementManager Block Model
 *
 * @method \Webkul\MpAdvertisementManager\Model\ResourceModel\Block _getResource()
 * @method \Webkul\MpAdvertisementManager\Model\ResourceModel\Block getResource()
 */
class Block extends \Magento\Framework\Model\AbstractModel implements BlockInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * mobikul api cache tag
     */
    const CACHE_TAG = 'marketplace_ads_block';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_ads_block';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_ads_block';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAdvertisementManager\Model\ResourceModel\Block');
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
     * @return \Webkul\MpAdvertisementManager\Model\Block
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
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created Time
     *
     * @param  string $createdAt
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set updated Time
     *
     * @param  string $updatedAt
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
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
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get seller id
     *
     * @return int
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set seller id
     *
     * @param  int $sellerId
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent()
    {
        return parent::getData(self::CONTENT);
    }

    /**
     * Set Content
     *
     * @param  string $content
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setContent($content)
    {
        return parent::setData(self::CONTENT, $content);
    }

    /**
     * Get AddedBy
     *
     * @return float|null
     */
    public function getAddedBy()
    {
        return parent::getData(self::ADDED_BY);
    }

    /**
     * Set AddedBy
     *
     * @param  float $addedBy
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setAddedBy($addedBy)
    {
        return parent::setData(self::ADDED_BY, $addedBy);
    }
}
