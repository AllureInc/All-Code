<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Magento\Framework\DataObject\IdentityInterface;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection;
use Plenty\Item\Api\Data\Import\Item\PropertyInterface;

/**
 * Class Property
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Property getResource()
 * @method Collection getCollection()
 */
class Property extends ImportExportAbstract implements PropertyInterface,
   IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_property';
    protected $_cacheTag        = 'plenty_item_import_item_property';
    protected $_eventPrefix     = 'plenty_item_import_item_property';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Property::class);
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

    public function getPlentyEntityId()
    {
        return $this->getData(self::PLENTY_ENTITY_ID);
    }

    public function getPropertyId()
    {
        return $this->getData(self::PROPERTY_ID);
    }

    public function getPropertySelectionId()
    {
        return $this->getData(self::PROPERTY_SELECTION_ID);
    }

    /**
     * @return array
     */
    public function getNames() : array
    {
        if (!$names = $this->getData(self::NAMES)) {
            return [];
        }
        return $this->_serializer->unserialize($names);
    }

    /**
     * @return array
     */
    public function getPropertySelections() : array
    {
        if (!$propertySelections = $this->getData(self::PROPERTY_SELECTION)) {
            return [];
        }
        return $this->_serializer->unserialize($propertySelections);
    }

    /**
     * @return array
     */
    public function getProperty() : array
    {
        if (!$properties = $this->getData(self::PROPERTY)) {
            return [];
        }
        return $this->_serializer->unserialize($properties);
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
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationProperties(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}