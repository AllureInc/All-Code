<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\CategoryInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\Category\Collection;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Category
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Category getResource()
 * @method Collection getCollection()
 */
class Category extends ImportExportAbstract implements CategoryInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_category';
    protected $_cacheTag        = 'plenty_item_import_item_category';
    protected $_eventPrefix     = 'plenty_item_import_item_category';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Category::class);
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

    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getIsNeckermannPrimary()
    {
        return $this->getData(self::IS_NECKERMANN_PRIMARY);
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
    public function getVariationCategories(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}