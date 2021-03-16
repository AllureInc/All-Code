<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * DataLayer Class
 * For providing product's data
 */
namespace Cnnb\Gtm\DataLayer\ProductData;

/**
 * ProductProvider | DataLayer Class
 */
class ProductProvider extends ProductAbstract
{
    /**
     * @param array $productProviders
     */
    public function __construct(
        array $productProviders = []
    ) {
        $this->productProviders = $productProviders;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data =  $this->getProductData();
        $arraysToMerge = [];

        /** @var ProductAbstract $productProvider */
        foreach ($this->getProductProviders() as $productProvider) {
            $productProvider->setProduct($this->getProduct())->setProductData($data);
            $arraysToMerge[] = $productProvider->getData();
        }

        return empty($arraysToMerge) ? $data : array_merge($data, ...$arraysToMerge);
    }
}
