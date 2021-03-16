<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Texts\Collection;

/**
 * Class Texts
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Texts getResource()
 * @method Collection getCollection()
 */
class Texts extends ImportExportAbstract implements \Plenty\Item\Api\Data\Import\Item\TextsInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_texts';
    protected $_cacheTag        = 'plenty_item_import_item_texts';
    protected $_eventPrefix     = 'plenty_item_import_item_texts';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Texts::class);
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

    public function getLang()
    {
        return $this->getData(self::LANG);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function getName2()
    {
        return $this->getData(self::NAME2);
    }

    public function getName3()
    {
        return $this->getData(self::NAME3);
    }

    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function getTechnicalData()
    {
        return $this->getData(self::TECHNICAL_DATA);
    }

    public function getUrlPath()
    {
        return $this->getData(self::URL_PATH);
    }

    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
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
     * @param null $lang
     * @return Collection
     */
    public function getVariationTexts(Variation $variation, $lang = null) : Collection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId());
        if (null !== $lang) {
            $collection->addFieldToFilter('lang', $lang);
        }
        return $collection;
    }
}