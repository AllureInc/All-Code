<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade SolrBridge to newer
 * versions in the future.
 *
 * @category    SolrBridge
 * @package     SolrBridge_Search
 * @author      Hau Danh
 * @copyright   Copyright (c) 2011-2017 SolrBridge (http://www.solrbridge.com)
 */
namespace Solrbridge\Search\Block\Result;

use Solrbridge\Search\Helper\System as System;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $_solrSearchResultData = null;
    
    public function setSolrSearchResultData($data)
    {
        $this->_solrSearchResultData = $data;
        return $this;
    }
    
    public function _getSolrSearchResultData()
    {
        if (null === $this->_solrSearchResultData) {
            $this->_solrSearchResultData = System::getRegistry()->registry('solrbridge_search_result_data');
        }
        return $this->_solrSearchResultData;
    }
    
    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        $collection = $this->_getProductCollection();
        
        $result = $this->_getSolrSearchResultData();
        $foundProductIds = $result['productids'];
        $totalRecords = $result['recordcount'];
        
        $collection->setFlag('SOLRBRIDGE_SEARCH_PRODUCT_COLLECTION_SIZE', $totalRecords);
        
        return $collection;
    }
}
