<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Request;

use Magento\Catalog\Model\Product as CatalogProduct;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;

/**
 * Interface VariationDataInterface
 * @package Plenty\Item\Rest\Request
 */
interface VariationDataInterface extends \Plenty\Item\Rest\AbstractData\VariationDataInterface
{
    /**
     * @return ProductExportInterface
     * @throws \Exception
     */
    public function getProfileEntity() : ProductExportInterface;

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity);

    /**
     * @param CatalogProduct $product
     * @return mixed
     */
    public function buildRequest(CatalogProduct $product);
}