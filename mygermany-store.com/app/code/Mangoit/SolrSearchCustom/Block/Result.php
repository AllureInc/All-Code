<?php
/**
 * DISCLAIMER
 *
 *
 * @category    Mangoit
 * @package     Mangoit_SolrSearchCustom
 * @author      Mangoit
 * @copyright   Copyright (c) 2011-2017 Mangoit
 */
namespace Mangoit\SolrSearchCustom\Block;

use Solrbridge\Search\Helper\Utility;

class Result extends \Solrbridge\Search\Block\Result
{
	/**
     * Retrieve No Result or Minimum query length Text
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getNoResultText()
    {
        if ($this->catalogSearchData->isMinQueryLength()) {
            return __('Minimum Search query length is %1', $this->_getQuery()->getMinQueryLength());
        }
        // return $this->_getData('no_result_text');
        return __('Unfortunately no products were found according to your search criteria.');
    }

    protected function _getFilterQuery()
    {
        $filterQuery = $this->getRequest()->getParam('fq');
        $returnFilterQuery = array();
        if (is_array($filterQuery) && count($filterQuery) > 0) {
            $returnFilterQuery = $filterQuery;
        }
        /**********Code added for country restriction*************/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $restrictedHelper = $objectManager->create('Mangoit\FskRestricted\Helper\Data');
        $countryName = $restrictedHelper->getCurrentCountry();  
        $returnFilterQuery['restricted_countries'] = [$countryName];
        /**********Code added for country restriction*************/

        return $returnFilterQuery;
    }

        protected function _requestSolrSearch()
    {
        //die(__FILE__.':'.__FUNCTION__);
        $queryText = $this->catalogSearchData->getEscapedQueryText();
        $filterQuery = $this->_getFilterQuery();
        
        $currentPage = $this->getListBlock()->getToolbarBlock()->getCurrentPage();
        $limit = $this->getListBlock()->getToolbarBlock()->getLimit();
        $currentSort = $this->getListBlock()->getToolbarBlock()->getCurrentOrder();
        
        if($currentSort !== $this->getRequest()->getParam('product_list_order')) {
            $currentSort = null;
        }
        
        $currentDirection = $this->getListBlock()->getToolbarBlock()->getCurrentDirection();
        
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
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $solrQuery = new \Mangoit\SolrSearchCustom\Library\Solr\Client\Query();
        $solrQuery->setIndex($index);
        $solrQuery->setLimit($limit);
        $solrQuery->setCurrentPage($currentPage);
        $solrQuery->setQueryText($queryText);
        $solrQuery->setFilterQuery($filterQuery);
        $solrQuery->setSort($currentSort, $currentDirection);
        $result = $solrQuery->execute();
        Utility::prepareFacetData($result, $this->_storeManager->getStore(), $queryText);
        
        if (isset($result['didyoumean']) && !empty($result['didyoumean'])) {
            $this->_didYouMean = $result['didyoumean'];
        }
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('Magento\Framework\Registry');
        $registry->register('solrbridge_search_result', $result);
        $registry->register('solrbridge_search_query_model', $solrQuery);
        
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
}
