<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\SalesPriceInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice\Collection;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class SalesPrice
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice getResource()
 * @method Collection getCollection()
 */
class SalesPrice extends ImportExportAbstract implements SalesPriceInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_salesprice';
    protected $_cacheTag        = 'plenty_item_import_item_salesprice';
    protected $_eventPrefix     = 'plenty_item_import_item_salesprice';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\SalesPrice::class);
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

    public function getSalesPriceId()
    {
        return $this->getData(self::SALES_PRICE_ID);
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
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
     * @param null $priceId
     * @return Collection
     */
    public function getVariationSalesPrices(Variation $variation, $priceId = null) : Collection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId());
        if (null !== $priceId) {
            $collection->addFieldToFilter(self::SALES_PRICE_ID, $priceId);
        }
       return $collection;
    }
}