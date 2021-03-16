<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request\Item;

use Magento\Catalog\Model\Product as CatalogProduct;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;

/**
 * Interface TextDataInterface
 * @package Plenty\Item\Rest\Request\Item
 */
interface TextDataInterface extends \Plenty\Item\Rest\AbstractData\Item\TextDataInterface
{

    /**
     * @return ProductExportInterface
     */
    public function getProfileEntity() : ProductExportInterface;

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity);

    /**
     * @return array
     */
    public function getRequest();

    /**
     * @param CatalogProduct $product
     * @return mixed
     */
    public function buildBatchRequest(CatalogProduct $product);

    /**
     * @return array
     */
    public function getErrors();
}