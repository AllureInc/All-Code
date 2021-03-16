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
namespace Solrbridge\Search\Block;

use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class Result extends \Magento\CatalogSearch\Block\Result
{
    const SOLRBRIDGE_RESULT_CACHE_PREFIX = 'SOLRBRIDGE_SOLR_RESULT_CACHE_';
    
    protected $_helper = null;
    protected $_didYouMean = null;
    
    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        //Request to solr api for search result
        $this->_requestSolrSearch();

        return parent::_prepareLayout();
    }
    
    protected function getCatalogSearchData()
    {
        if (null === $this->_helper) {
            $this->_helper = System::getObjectManager()->get('Solrbridge\Search\Helper\Data');
        }
        return $this->_helper;
    }
    
    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        $catalogSearchData = $this->getCatalogSearchData();
        if ($this->_didYouMean) {
            $catalogSearchData->setDidYouMeanQueryText($this->_didYouMean);
            return __("Search results for: '%1' instead", $catalogSearchData->getEscapedQueryText());
        }
        return __("Search results for: '%1'", $catalogSearchData->getEscapedQueryText());
    }
    
    protected function _getIndex()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $indexModel = $objectManager->create('Solrbridge\Search\Model\Index');
        $collection = $indexModel->getCollection();
        $collection->addFieldToFilter('store_id', array('eq' => $storeId));
        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }
        return null;
    }
    
    public function getCatalogConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Catalog\Model\Config');
    }
    
    protected function _getFilterQuery()
    {
        $filterQuery = $this->getRequest()->getParam('fq');
        $returnFilterQuery = array();
        if (is_array($filterQuery) && count($filterQuery) > 0) {
            $returnFilterQuery = $filterQuery;
        }
        return $returnFilterQuery;
    }
    
    protected function _requestSolrSearch()
    {
        $queryParamName = $this->catalogSearchData->getQueryParamName();
        $queryText = $this->getRequest()->getParam($queryParamName);
        //$this->catalogSearchData->getEscapedQueryText();
        $filterQuery = $this->_getFilterQuery();
        
        $currentPage = 1;
        $toolbarBlock = $this->getListBlock()->getToolbarBlock();
        if ($toolbarBlock) {
            $currentPage = $toolbarBlock->getCurrentPage();
        }
        
        $limit = 1;
        if ($toolbarBlock) {
            $limit = $toolbarBlock->getLimit();
        }
        
        $currentSort = null;
        if ($toolbarBlock) {
            $currentSort = $toolbarBlock->getCurrentOrder();
        }
        
        if($currentSort !== $this->getRequest()->getParam('product_list_order')) {
            $currentSort = null;
        }
        
        $currentDirection = null;
        if ($currentSort) {
            $currentDirection = $toolbarBlock->getCurrentDirection();
        }
        
        $usedForSortByAttrs = $this->getCatalogConfig()->getAttributesUsedForSortBy();
        
        //$currentSort = 'name';
        if (array_key_exists($currentSort, $usedForSortByAttrs)) {
            $sortAttribute = $usedForSortByAttrs[$currentSort];
            $currentSort = $sortAttribute->getAttributeCode().'_'.$sortAttribute->getBackendType();
        } elseif ($currentSort == 'position') {
            //$currentSort = 'cat_position_int';
            $currentSort = null;
            //@TODO: temporary - No sorting here because position determined by solr search boost and search weight
        } else {
            //@TODO: what? if this case?
        }
        $index = $this->_getIndex();
        if ($index) {
            //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $solrQuery = new \Solrbridge\Solr\Library\Client\Query();
            $solrQuery->setIndex($index);
            $solrQuery->setLimit($limit);
            $solrQuery->setCurrentPage($currentPage);
            $solrQuery->setQueryText($queryText);
            $solrQuery->setFilterQuery($filterQuery);
            
            if ($currentSort) {
                $solrQuery->setSort($currentSort, $currentDirection);
            }
            $result = $solrQuery->execute();
        }
        
        if ($result) {
            Utility::prepareFacetData($result, $this->_storeManager->getStore(), $queryText);
    
            if (isset($result['didyoumean']) && !empty($result['didyoumean'])) {
                $this->_didYouMean = $result['didyoumean'];
            }
    
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $registry = $objectManager->get('Magento\Framework\Registry');
            $registry->register('solrbridge_search_result', $result);
            //$registry->register('solrbridge_search_query_model', $solrQuery);
    
            $data = array('productids' => array(), 'recordcount' => 0);
    
            if ($result && is_array($result) && isset($result['response']['numFound'])) {
                foreach ($result['response']['docs'] as $doc) {
                    $data['productids'][] = $doc['document_id'];
                }
                $data['recordcount'] = $result['response']['numFound'];
            }
    
            $registry->register('solrbridge_search_result_data', $data);
    
            return $data;
        }
        
        return [];
    }
}
