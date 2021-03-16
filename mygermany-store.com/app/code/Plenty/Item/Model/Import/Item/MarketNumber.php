<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\MarketNumberInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber\Collection;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class MarketNumber
 * @package Plenty\Item\Model\Import\Item
 *
 *  @method \Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber getResource()
 * @method Collection getCollection()
 */
class MarketNumber extends ImportExportAbstract implements MarketNumberInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_market_number';
    protected $_cacheTag        = 'plenty_item_import_item_market_number';
    protected $_eventPrefix     = 'plenty_item_import_item_market_number';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\MarketNumber::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getPlentyEntityId() : int
    {
        return $this->getData(self::PLENTY_ENTITY_ID);
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

    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getValue()
    {
        return $this->getData(self::VALUE);
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
    public function getVariationMarketNumbers(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}