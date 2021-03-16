<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Export;

use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Model\Export\Product as ProductExportModel;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogProductCollection;

/**
 * Class Product
 * @package Plenty\Item\Model\ResourceModel\Export
 */
class Product extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init('plenty_item_export_product', 'entity_id');
    }

    /**
     * @param $profileId
     * @param CatalogProductCollection $productCollection
     * @return $this
     * @throws \Exception
     */
    public function saveProducts($profileId, CatalogProductCollection $productCollection)
    {
        $products = [];
        /** @var CatalogProduct $product */
        foreach ($productCollection as $product) {
            $product->setData('profile_id', $profileId);
            $products[] = $this->_prepareProductData($product);
        }

        try {
            $this->getConnection()->insertOnDuplicate($this->getMainTable(), $products);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param CatalogProduct $product
     * @return array
     */
    private function _prepareProductData(CatalogProduct $product)
    {
        if (!$profileId = $product->getData('profile_id')) {
            return array();
        }

        $data = [
            'profile_id'            => $profileId,
            'sku'                   => $product->getSku(),
            'name'                  => $product->getName(),
            'product_id'            => $product->getId(),
            'item_id'               => $product->getPlentyItemId(),
            'variation_id'          => $product->getPlentyVariationId(),
            'product_type'          => $product->getTypeId(),
            'attribute_set'         => $product->getDefaultAttributeSetId(),
            'visibility'            => $product->getVisibility(),
            'status'                => Status::PENDING,
            'created_at'            => $this->_date->gmtDate(),
            'message'              => __('Product has been added to export'),
            'updated_at'            => $this->_date->gmtDate()
        ];

        return $data;
    }

    /**
     * @param $table
     * @return $this
     */
    public function _truncateTable($table)
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($table);
        } else {
            $this->getConnection()->truncateTable($table);
        }
        return $this;
    }
}