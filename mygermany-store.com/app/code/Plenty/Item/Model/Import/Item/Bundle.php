<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Api\Data\Import\Item\BundleInterface;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Bundle\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Plenty\Item\Model\ResourceModel\Import\Item;

/**
 * Class Bundle
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Bundle getResource()
 * @method Collection getCollection()
 */
class Bundle extends ImportExportAbstract implements BundleInterface,
   IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_bundle';
    protected $_cacheTag        = 'plenty_item_import_item_bundle';
    protected $_eventPrefix     = 'plenty_item_import_item_bundle';

    protected function _construct()
    {
        $this->_init(Item\Bundle::class);
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

    public function getComponentVariationId()
    {
        return $this->getData(self::COMPONENT_VARIATION_ID);
    }

    public function getComponentSku()
    {
        return $this->getData(self::COMPONENT_SKU);
    }

    public function getComponentName()
    {
        return $this->getData(self::COMPONENT_NAME);
    }

    public function getComponentQty()
    {
        return $this->getData(self::COMPONENT_QTY);
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
    public function getBundleComponents(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addVariationFilter($variation->getVariationId())
            ->joinVariationEntries()
            ->load();
    }
}