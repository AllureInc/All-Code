<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\BarcodeInterface;
use Plenty\Item\Model\ResourceModel\Import\Item\Barcode\Collection;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Barcode
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Barcode getResource()
 * @method Collection getCollection()
 */
class Barcode extends ImportExportAbstract implements BarcodeInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_barcode';
    protected $_cacheTag        = 'plenty_item_import_item_barcode';
    protected $_eventPrefix     = 'plenty_item_import_item_barcode  ';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Barcode::class);
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

    public function getCode()
    {
        return $this->getData(self::CODE);
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
     * @param null $barcodeId
     * @param Variation $variation
     * @return Collection
     */
    public function getVariationBarcodes(Variation $variation, $barcodeId = null) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }
}