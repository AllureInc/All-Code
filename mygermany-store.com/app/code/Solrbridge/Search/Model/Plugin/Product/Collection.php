<?php

namespace Solrbridge\Search\Model\Plugin\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogProductCollection;

class Collection
{
    public function afterGetSize(CatalogProductCollection $collection, $size)
    {
        if ($collection->hasFlag('SOLRBRIDGE_SEARCH_PRODUCT_COLLECTION_SIZE')) {
            $size = $collection->getFlag('SOLRBRIDGE_SEARCH_PRODUCT_COLLECTION_SIZE');
        }
        return $size;
    }
}