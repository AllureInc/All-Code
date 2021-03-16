<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\ShippingProfileInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\ShippingProfile\Collection;
use Magento\Framework\DataObject\IdentityInterface;


/**
 * Class ShippingProfile
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\ShippingProfile getResource()
 * @method Collection getCollection()
 */
class ShippingProfile extends ImportExportAbstract implements ShippingProfileInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_shipping_profile';
    protected $_cacheTag        = 'plenty_item_import_item_shipping_profile';
    protected $_eventPrefix     = 'plenty_item_import_item_shipping_profile';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\ShippingProfile::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getPlentyEntityId() : int
    {
        return $this->getData(self::PLENTY_ENTITY_ID);
    }

    public function getProfileId() : int
    {
        return $this->getData(self::PROFILE_ID);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return Collection
     */
    public function getItemShippingProfiles(\Plenty\Item\Model\Import\Item $item) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::ITEM_ID, $item->getItemId())
            ->load();
    }
}