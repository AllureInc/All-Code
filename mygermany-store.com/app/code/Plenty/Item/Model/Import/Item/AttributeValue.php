<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\AttributeValueInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue\Collection;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class AttributeValue
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue getResource()
 * @method Collection getCollection()
 */
class AttributeValue extends ImportExportAbstract implements AttributeValueInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_attribute_value';
    protected $_cacheTag        = 'plenty_item_import_item_attribute_value';
    protected $_eventPrefix     = 'plenty_item_import_item_attribute_value';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\AttributeValue::class);
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

    public function getAttributeValueSetId()
    {
        return $this->getData(self::ATTRIBUTE_VALUE_SET_ID);
    }

    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    public function getValueId()
    {
        return $this->getData(self::VALUE_ID);
    }

    public function getIsLinkedToImage()
    {
        return $this->getData(self::IS_LINKED_TO_IMAGE);
    }

    public function getAttributeBackendName()
    {
        return $this->getData(self::ATTRIBUTE_BACKEND_NAME);
    }

    public function getAttributePosition()
    {
        return $this->getData(self::ATTRIBUTE_POSITION);
    }

    public function getValueBackendName()
    {
        return $this->getData(self::VALUE_BACKEND_NAME);
    }

    public function getValuePosition()
    {
        return $this->getData(self::VALUE_POSITION);
    }

    public function getIsSurchargePercentage()
    {
        return $this->getData(self::IS_SURCHARGE_PERCENTAGE);
    }

    public function getAmazonAttribute()
    {
        return $this->getData(self::AMAZON_ATTRIBUTE);
    }

    public function getFruugoAttribute()
    {
        return $this->getData(self::FRUUGO_ATTRIBUTE);
    }

    public function getPixmaniaAttribute()
    {
        return $this->getData(self::PIXMANIA_ATTRIBUTE);
    }

    public function getOttoAttribute()
    {
        return $this->getData(self::OTTO_ATTRIBUTE);
    }

    public function getGoogleShoppingAttribute()
    {
        return $this->getData(self::GOOGLE_SHOPPING_ATTRIBUTE);
    }

    public function getNeckermannAtEpAttribute()
    {
        return $this->getData(self::NECKERMANN_AT_EP_ATTRIBUTE);
    }

    public function getTypeOfSelectionInOnlineStore()
    {
        return $this->getData(self::TYPE_OF_SELECTION_IN_ONLINE_STORE);
    }

    public function getLaRedouteAttribute()
    {
        return $this->getData(self::LA_REDOUTE_ATTRIBUTE);
    }

    public function getIsGroupable()
    {
        return $this->getData(self::IS_GROUPABLE);
    }

    public function getValueImage()
    {
        return $this->getData(self::VALUE_IMAGE);
    }

    public function getValueComment()
    {
        return $this->getData(self::VALUE_COMMENT);
    }

    public function getAttributeValue()
    {
        return $this->getData(self::ATTRIBUTE_VALUE);
    }

    public function getAmazonValue()
    {
        return $this->getData(self::AMAZON_VALUE);
    }

    public function getOttoValue()
    {
        return $this->getData(self::OTTO_VALUE);
    }

    public function getNeckermannAtEpValue()
    {
        return $this->getData(self::NECKERMANN_AT_EP_VALUE);
    }

    public function getLaRedouteValue()
    {
        return $this->getData(self::LA_REDOUTE_VALUE);
    }

    public function getTracdelightvalue()
    {
        return $this->getData(self::TRACDELIGHT_VALUE);
    }

    public function getPercentageDistribution()
    {
        return $this->getData(self::PERCENTAGE_DISTRIBUTION);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getAttributeUpdatedAt()
    {
        return $this->getData(self::ATTRIBUTE_UPDATED_AT);
    }

    public function getValueUpdatedAt()
    {
        return $this->getData(self::VALUE_UPDATED_AT);
    }

    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationAttributeValues(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}