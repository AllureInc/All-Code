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
namespace Solrbridge\Search\Block\Catalog\Category;

use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;

class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $this->requestSolrSearch();
        
        return $this;
    }
    
    protected function getListBlock()
    {
        return $this->getChildBlock('product_list');
    }
    
    protected function getFilterQuery()
    {
        $filterQuery = $this->getRequest()->getParam('fq');
        $returnFilterQuery = array();
        if (is_array($filterQuery) && count($filterQuery) > 0) {
            $returnFilterQuery = $filterQuery;
        }
        //print_r($returnFilterQuery);
        $currentCategory = $this->getCurrentCategory();
        if ($currentCategory->getId() > 0) {
            $returnFilterQuery['cat'] = array($currentCategory->getId());
        }
        return $returnFilterQuery;
    }
    
    public function getCatalogConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Catalog\Model\Config');
    }
    
    protected function requestSolrSearch()
    {
        $filterQuery = $this->getFilterQuery();
        //print_r($filterQuery);
        $currentPage = $this->getListBlock()->getToolbarBlock()->getCurrentPage();
        $limit = $this->getListBlock()->getToolbarBlock()->getLimit();
        $currentSort = $this->getListBlock()->getToolbarBlock()->getCurrentOrder();
        $currentDirection = $this->getListBlock()->getToolbarBlock()->getCurrentDirection();
        
        $usedForSortByAttrs = $this->getCatalogConfig()->getAttributesUsedForSortBy();
        
        //$currentSort = 'name';
        if (array_key_exists($currentSort, $usedForSortByAttrs)) {
            $sortAttribute = $usedForSortByAttrs[$currentSort];
            $currentSort = $sortAttribute->getAttributeCode().'_'.$sortAttribute->getBackendType();
        } elseif ($currentSort == 'position') {
            //$currentSort = 'cat_position_int';
            $currentSort = 'cat_'.$this->getCurrentCategory()->getId().'_position_int';
            //@TODO: temporary - No sorting here because position determined by solr search boost and search weight
        } else {
            //@TODO: what? if this case?
        }
        
        
        $index = $this->getIndex();
        
        $solrQuery = new \Solrbridge\Solr\Library\Client\Query();
        $solrQuery->setIndex($index);
        $solrQuery->setLimit($limit);
        $solrQuery->setCurrentPage($currentPage);
        $solrQuery->setQueryText('*:*');
        $solrQuery->setMM('0%');
        $solrQuery->setFilterQuery($filterQuery);
        $solrQuery->setSort($currentSort, $currentDirection);
        $result = $solrQuery->execute();
        
        Utility::prepareFacetData($result, $this->_storeManager->getStore(), $this->getCurrentCategory()->getId());
        
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
    
    protected function getIndex()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $indexModel = $objectManager->create('Solrbridge\Search\Model\Index');
        $collection = $indexModel->getCollection();
        $collection->addFieldToFilter('store_id', array('eq' => $storeId));
        return $collection->getFirstItem();
    }
}
