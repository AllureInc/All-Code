<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\ResourceModel\Import\Item\Supplier\Collection;
use Plenty\Item\Api\Data\Import\Item\SupplierInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Supplier
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Supplier getResource()
 * @method Collection getCollection()
 */
class Supplier extends ImportExportAbstract implements SupplierInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_supplier';
    protected $_cacheTag        = 'plenty_item_import_item_supplier';
    protected $_eventPrefix     = 'plenty_item_import_item_supplier';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Supplier::class);
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

    public function getSupplierId()
    {
        return $this->getData(self::SUPPLIER_ID);
    }

    public function getPurchasedPrice()
    {
        return $this->getData(self::PURCHASE_PRICE);
    }

    public function getMinimumPurchase()
    {
        return $this->getData(self::MINIMUM_PURCHASE);
    }

    public function getItemNumber()
    {
        return $this->getData(self::ITEM_NUMBER);
    }

    public function getLastPriceQuery()
    {
        return $this->getData(self::LAST_PRICE_QUERY);
    }

    public function getDeliveryTimeInDays()
    {
        return $this->getData(self::DELIVERY_TIME_IN_DAYS);
    }

    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }

    public function getIsDiscountable()
    {
        return $this->getData(self::IS_DISCOUNTABLE);
    }

    public function getPackagingUnit()
    {
        return $this->getData(self::PACKAGING_UNIT);
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
    public function getVariationSuppliers(Variation $variation) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::VARIATION_ID, $variation->getVariationId())
            ->load();
    }

    public function getSupplierNameById($id)
    {
        $supplier = $this->getSupplierById($id);

        return $supplier['companyName'];
    }


}