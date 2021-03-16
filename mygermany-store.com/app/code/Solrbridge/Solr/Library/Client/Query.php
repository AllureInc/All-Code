<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Solrbridge\Solr\Library\Client;
use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;
use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;
use Magento\Framework\Serialize\Serializer\Json;

class Query extends \Solrbridge\Solr\Library\Client
{
    protected $_queryUrl = null;
    
    protected $_queryText = '*:*';
    
    protected $_queryFields = 'textSearchStandard^80 textSearch^40 textSearchText^10 textSearchGeneral^1';
    
    protected $_limit = 20;
    
    protected $_offset = 0;
    
    protected $_sortField = null;
    protected $_sortDirection = null;
    
    protected $_page = 1;
    
    protected $_fieldList = array(
        'document_id',
        'document_type',
        'store_id',
        'website_id',
        'name_varchar',
        //'price_decimal',
        //'price_facet'
    );
    
    protected $_rangeFields = array();
    
    protected $_boostFields = null;
    
    protected $_facetLimit = -1;
    
    protected $_multipleFilterAttributes = array();
    
    protected $_mm = '100%';
    
    protected $_arguments = array();
    
    protected $_filterQuery = array();
    
    protected $_storeId = null;
    
    protected $_helper;
    
    protected $cache;
    
    protected $serializer;
    
    protected $_priceRangeGap = 0;
    
    public function __construct()
    {
        $this->_helper = System::getObjectManager()->get('Solrbridge\Search\Helper\Data');
        $this->cache = System::getObjectManager()->get('Magento\Framework\App\CacheInterface');
        $this->serializer = System::getObjectManager()->get(Json::class);
    }
    
    public function setMM($mm)
    {
        $this->_mm = $mm;
        return $this;
    }
    
    public function getQueryText()
    {
        if('100%' == $this->_mm) {
            return '"'.trim($this->_queryText, '"').'"';
        }
        return $this->_queryText;
    }
    
    public function setQueryText($query)
    {
        $this->_queryText = $query;
        return $this;
    }
    
    public function setFilterQuery($filterQuery = array())
    {
        $this->_filterQuery = $filterQuery;
        return $this;
    }
    
    public function getFilterQuery()
    {
        return $this->_filterQuery;
    }
    
    public function setLimit($limit) {
        $this->_limit = $limit;
        return $this;
    }
    
    public function getLimit() {
        return $this->_limit;
    }
    
    public function setCurrentPage($page = 1) {
        $this->_page = $page;
        return $this;
    }
    
    public function setSort($fieldName, $direction)
    {
        if ($fieldName) {
            $this->_sortField = $fieldName;
            $this->_sortDirection = $direction;
        }
        return $this;
    }
    
    public function getPage() {
        return $this->_page;
    }
    
    public function getOffset() {
        return ($this->getPage() - 1) * $this->getLimit();
    }
    
    protected function _buildQueryUrl()
    {
        $this->_queryUrl = $this->buildUrl('select', $this->getIndex()->getSolrCore());
        return $this->_queryUrl;
    }
    
    protected function _prepareQueryArgument()
    {
        $this->_arguments = array(
            'q' => $this->getQueryText(),
            'json.nl' => 'map',
            'rows' => $this->getLimit(),
            'start' => $this->getOffset(),
            'fl' => @implode(',', $this->_fieldList),
            'qf' => $this->_queryFields,
            'spellcheck' => 'true',
            'spellcheck.collate' => 'true',
            //'facet' => 'true',
            //'facet.mincount' => 1,
            //'facet.limit' => $this->_facetLimit,
            'timestamp' => time(),
            'mm' => $this->_mm,
            'defType'=> 'edismax',
            'wt'=> 'json',
        );
        return $this->_arguments;
    }
    
    protected function getFilterKey($key)
    {
        if($key == 'price') {
            $key .= '_decimal';
        } else {
            $key .= '_facet';
        }
        return $key;
    }
    
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }
    
    public function getStoreId()
    {
        if($this->_storeId === null)
        {
            $this->_storeId = $this->getIndex()->getStoreId();
        }
        return $this->_storeId;
    }
    
    protected function _prepareFilterQuery()
    {
        $fq = '(store_id:'.$this->getStoreId().')';
    
        $filters = $this->_filterQuery;
        if (is_array($filters) && count($filters) > 0) {
            foreach ($filters as $field => $value) {
                if ( !is_array($value) ) {
                    $fq .= ' AND (' . $this->getFilterKey($field).':"' . urldecode($value) .'")';
                } else {
                    if ($field == 'price') {
                        $part = '';
                        foreach ($value as $val) {
                            $val = str_replace('-', ' TO ', $val);
                            $part .= '('.$this->getFilterKey($field).':['.urldecode(trim($val).'.99999').']) OR ';
                        }
                        $part = trim(trim($part), 'OR');
                        if ( !empty($part) ) {
                            $fq .= ' AND ('.trim($part).')';
                        }
                    } else {
                        $part = '';
                        foreach ($value as $val) {
                            $part .= '('.$this->getFilterKey($field).':"'.urldecode($val).'") OR ';
                        }
                        $part = trim(trim($part), 'OR');
                        if ( !empty($part) ) {
                            $fq .= ' AND ('.trim($part).')';
                        }
                    }
                }
            }
        }
        
        $filterQueryArguments = array('fq' => $fq);
        $this->_arguments = array_merge($this->_arguments, $filterQueryArguments);
    }
    
    protected function _getFacetFields($cacheKey = null, $cacheKeyMultipleSelectAttrs = null)
    {
        if ($cacheKeyMultipleSelectAttrs) {
            $cachedMultipleSelectAttrs = $this->cache->load($cacheKeyMultipleSelectAttrs);
            if ($cachedMultipleSelectAttrs) {
                $this->_multipleFilterAttributes = $this->serializer->unserialize($cachedMultipleSelectAttrs);
            }
        }
        
            
        if ($cacheKey) {
            $cachedFacetFields = $this->cache->load($cacheKey);
            if ($cachedFacetFields) {
                return $this->serializer->unserialize($cachedFacetFields);
            }
        }
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('Magento\Framework\Registry');
        if (null !== $registry->registry('search_facet_fields')) {
            return $registry->registry('search_facet_fields');
        }
        return null;
    }
    
    public function getRangeFields()
    {
        //inject price facet - filters
        if ($this->_helper->getLayerNavSetting('price_filter/enable')) {
            $this->_rangeFields[] = 'price_decimal';
        }
        return $this->_rangeFields;
    }
    
    protected function _prepareFacets()
    {
        $cacheKey = 'solrbridge_search_facet_fields_store_'.$this->getStoreId();
        $cacheKeyMultipleSelectAttrs = 'solrbridge_search_multiple_select_attr_store_'.$this->getStoreId();
        $facetFields = $this->_getFacetFields($cacheKey, $cacheKeyMultipleSelectAttrs);
        if (!$facetFields) {
            $attributeCollection = $this->getIndex()->getDoctypeHandlerModel()->getProductAttributeCollection();
            
            if($this->_helper->isCategoryViewPage()) {
                //Filter attributes for catalog category view
                $attributeCollection->addIsFilterableFilter();
            } else {
                //Filter attributes for search result page
                $attributeCollection->addIsFilterableInSearchFilter();
            }
        
            foreach ($attributeCollection as $attribute) {
                //$facetFields[] = $attribute->getAttributeCode().'_'.$attribute->getBackendType();
                $facetFields[] = $attribute->getAttributeCode().'_facet';
                
                //Collect attributes which allow multiple filters
                if ((boolean)$attribute->getSolrbridgeSearchMultipleFilter()) {
                    $this->_multipleFilterAttributes[] = $attribute->getAttributeCode();
                }
            }
        }
        
        if(!is_array($facetFields)) {
            $facetFields = array();
        } else {
            $this->cache->save($this->serializer->serialize($this->_multipleFilterAttributes), $cacheKeyMultipleSelectAttrs, ['solrbridge_search_query'], 3600);
            $this->cache->save($this->serializer->serialize($facetFields), $cacheKey, ['solrbridge_search_query'], 3600);
        }
        
        //inject category into facet fields
        $facetFields = array_merge(array(DoctypeHandler::CATEGORY_PATH_KEY), $facetFields);
        
        if (is_array($facetFields) && count($facetFields) > 0) {
            $enableFacetParams = array(
                'facet' => 'true',
                'facet.mincount' => 1,
                'facet.limit' => $this->_facetLimit,
            );
            $this->_arguments = array_merge($this->_arguments, $enableFacetParams);
            //Check if question exists in queryurl
            if (strpos($this->_queryUrl, '?')) {
                $this->_queryUrl .= '&facet.field='.@implode('&facet.field=', $facetFields);
            } else {
                $this->_queryUrl .= '?facet.field='.@implode('&facet.field=', $facetFields);
            }
        }
        
        return $facetFields;
    }
    
    protected function _prepareBoostFields()
    {
        $cacheKey = 'solrbridge_search_boost_fields_store_'.$this->getStoreId();
        
        $cachedBoostFields = $this->cache->load($cacheKey);
        if ($cachedBoostFields) {
            $this->_boostFields = $this->serializer->unserialize($cachedBoostFields);
        }
        
        if (null === $this->_boostFields) {
            $attributeCollection = $this->getIndex()->getDoctypeHandlerModel()->getProductAttributeCollection();
            $attributeCollection->addFieldToFilter('additional_table.solrbridge_search_boost_weight', array('gt' => 0));
            $attributeCollection->clear()->load();
            $queryText = $this->getQueryText();
            $queryText = urlencode($queryText);
            foreach ($attributeCollection as $attribute) {
                //$facetFields[] = $attribute->getAttributeCode().'_'.$attribute->getBackendType();
                //$this->_boostFields[] = $attribute->getAttributeCode().'_facet';
                $attributeCode = $attribute->getAttributeCode();
                $boostWeight = $attribute->getData('solr_search_field_weight');

                $this->_boostFields[$attributeCode][] = array(
                        'field' => $attributeCode.'_boost_exact',
                        'weight' => ((int)$boostWeight + 209),
                        'value' => $queryText,
                        'type' => 'absolute',
                );
                $this->_boostFields[$attributeCode][] = array(
                        'field'=>$attributeCode.'_boost',
                        'weight'=> ((int)$boostWeight + 206),
                        'value'=> $queryText,
                        'type' => 'relative',
                );
                $this->_boostFields[$attributeCode][] = array(
                        'field'=>$attributeCode.'_relative_boost',
                        'weight'=> ((int)$boostWeight + 202),
                        'value'=> $queryText,
                        'type' => 'relative',
                );
            }
            $this->cache->save($this->serializer->serialize($this->_boostFields), $cacheKey, ['solrbridge_search_query'], 3600);
        }
        if(!$this->_boostFields) {
            $this->_prepareDefaultBoostFields();
        }
        
        if (is_array($this->_boostFields) && count($this->_boostFields) > 0) {
            $queryText = $this->getQueryText();
            $queryText = urlencode($queryText);
            
            foreach ($this->_boostFields as $attributeCode => $boostFields) {
                foreach ($this->_boostFields[$attributeCode] as $index => $values) {
                    $this->_boostFields[$attributeCode][$index]['value'] = $queryText;
                }
            }
        }
        
        $this->_prepareBoostSearchFieldWeight();
        
        $boostFieldString = $this->_convertBoostFieldsToString();
        
        if (!empty($boostFieldString)) {
            if (strpos($this->_queryUrl, '?')) {
                $this->_queryUrl .= '&bq='.urlencode($boostFieldString);
            } else {
                $this->_queryUrl .= '?bq='.urlencode($boostFieldString);
            }
        }
    }
    
    /**
     * Get default boost settings
     * @param string $queryText
     * @return array
     */
    protected function _prepareDefaultBoostFields()
    {
        $boostText = $this->getQueryText();
        $boostText = urlencode($boostText);

        $this->_boostFields['name'] = array(
                array(
                    'field' => 'name_boost_exact',
                    'weight' => 120,
                    'value' => $boostText,
                    'type' => 'absolute',
                ),
                array(
                    'field' =>'name_boost',
                    'weight' =>100,
                    'value'=>$boostText,
                    'type' => 'absolute',
                ),
                array(
                    'field' =>'name_relative_boost',
                    'weight' =>80,
                    'value'=>$boostText,
                    'type' => 'relative',
                ),
        );
        /*
        $boostFieldsArr['category'] = array(
                array(
                        'field' => 'category_boost',
                        'weight' => 60,
                        'value' => $boostText,
                        'type' => 'relative',
                )
        );*/
        //return $boostFieldsArr;
    }
    
    protected function _prepareBoostSearchFieldWeight()
    {
        $searchWeights = $this->getDocumentSearchFieldWeights();
        if (is_array($searchWeights)) {
            foreach ($searchWeights as $weight){
                if (is_numeric($weight) && (int)$weight > 0) {
                    $searchWeightOption = array(
                            'field' => 'document_search_weight_int',
                            'weight' => (200 + ((int)$weight * 10)),
                            'value' => $weight,
                            'type' => 'absolute',
                    );
                    $this->_boostFields['document_search_weight_int'][] = $searchWeightOption;
                }
            }
        }
    }
    
    /**
     * Get product search weights facets
     * @return array
     */
    public function getDocumentSearchFieldWeights()
    {
        $returnData = array();
        $statsUrl = $this->buildUrl('select', $this->getIndex()->getSolrCore());
        $statsUrl .= '?q=*:*&rows=0&facet.field=document_search_weight_int&facet=true&json.nl=map&wt=json';
        $statsData = $this->doGetRequest($statsUrl);

        if (is_array($statsData) && isset($statsData['facet_counts']['facet_fields']['document_search_weight_int'])) {
            $weightsFacets = $statsData['facet_counts']['facet_fields']['document_search_weight_int'];
            if (is_array($weightsFacets)){
                foreach ($weightsFacets as $key=>$val){
                    $returnData[] = $key;
                }
            }
        }
        
        return $returnData;
    }
    
    /**
     * convert Boost Settings Array To String
     * @param array $boostFieldsArr
     * $boostFieldsArr = array(
     *                         'att1' => array(
     *                                         array('field' => 'field_x', 'weight' => 'n', 'value' => 'value'),
     *                                         array('field' => 'field_y', 'weight' => 'n', 'value' => 'value')
     *                                        )
     * @return string
     */
    protected function _convertBoostFieldsToString()
    {
        $boostQueryString = '';
    
        if (is_array($this->_boostFields) && !empty($this->_boostFields))
        {
            foreach( $this->_boostFields as $attributeCode => $configArray)//Foreach attributes
            {
                foreach ($configArray as $config) // Foreach attribute config
                {
                    $boostField = $config['field'];
                    $boostWeight = $config['weight'];
                    $boostValue = $config['value'];
    
                    if (!empty($boostValue))
                    {
                        if (isset($config['type']) && $config['type'] == 'absolute')
                        {
                            $boostQueryString .= $boostField.':"'.$boostValue.'"^'.$boostWeight.' ';
                        }
                        else
                        {
                            $boostQueryString .= $boostField.':'.$boostValue.'^'.$boostWeight.' ';
                        }
                    }
    
                }
            }
        }
    
        return $boostQueryString;
    }
    
    protected function _prepareRangeFields()
    {
        $rangeFields = $this->getRangeFields();
        if (is_array($rangeFields)) {
            $rangeFieldString = $this->_convertRangeFieldsToString();
        
            if (strpos($this->_queryUrl, '?')) {
                $this->_queryUrl .= '&'.$rangeFieldString;
            } else {
                $this->_queryUrl .= '?'.$rangeFieldString;
            }
        }
    }
    
    /**
     * Convert rangeFields from array to param string
     * @return string
     */
    protected function _convertRangeFieldsToString()
    {
        $rangeFieldString = '';
        
        $gapNumber = $this->_helper->getLayerNavSetting('price_filter/step');

        if (is_array($this->_rangeFields) && !empty($this->_rangeFields))
        {
            $rangeFieldString .= '&stats=true';
            foreach ($this->_rangeFields as $fieldItem)
            {
                /*
                if ($this->_priceRangeGap > 0 && $fieldItem == 'price_decimal') {
                    $gapNumber = $this->_priceRangeGap;
                }
                $rangeFieldString .= '&facet.field='.$fieldItem;
                $rangeFieldString .= '&stats.field='.$fieldItem;
                $rangeFieldString .= '&facet.range='.$fieldItem;
                $rangeFieldString .= '&f.'.$fieldItem.'.facet.range.start=0';
                $rangeFieldString .= '&f.'.$fieldItem.'.facet.range.end=1000000';
                $rangeFieldString .= '&f.'.$fieldItem.'.facet.range.gap='.$gapNumber;
                $rangeFieldString .= '&f.'.$fieldItem.'.facet.mincount=1';
                */
                $rangeFieldString .= '&facet.field='.$fieldItem;
                $rangeFieldString .= '&stats.field='.$fieldItem;
            }
        }
        if (!empty($rangeFieldString)) {
            $rangeFieldString = trim($rangeFieldString,'&');
        }
        return $rangeFieldString;
    }
    
    protected function _prepareSort()
    {
        if(!empty($this->_sortField) && !empty($this->_sortDirection)) {
            $sortParams = array(
                'sort' => $this->_sortField.' '.$this->_sortDirection
            );
            $this->_arguments = array_merge($this->_arguments, $sortParams);
        }
    }
    
    public function execute()
    {
        $this->_buildQueryUrl();
        $this->_prepareQueryArgument();
        //$queryUrl = $this->buildUrl('select', $this->getIndex()->getSolrCore());
        $this->_prepareFilterQuery();
        $this->_prepareFacets();
        $this->_prepareBoostFields();
        
        /*
        $priceGap = $this->queryPriceStatsAndCalculateGap();
        if ($priceGap > 0) {
            $this->_priceRangeGap = $priceGap;
        }*/
        
        $this->_prepareRangeFields();
        
        $this->_prepareSort();
        
        //1 - Search with absolute match
        $result = $this->doGetRequest($this->_queryUrl, $this->_arguments);
        //2 - if no result found, then serch relative match
        if(!$this->_resultFoundOk($result)) {
            $this->_mm = '0%';
            $this->_prepareQueryArgument();
            $result = $this->doGetRequest($this->_queryUrl, $this->_arguments);
        }
        //3 - if no result found, then spellcheck 
        if(!$this->_resultFoundOk($result)) {
            //Spellcheck query
            $suggestedQuery = $this->_getSuggestedSpellText($result);
            $this->setQueryText($suggestedQuery);
            $this->_prepareQueryArgument();
            $result = $this->doGetRequest($this->_queryUrl, $this->_arguments);
            $result['didyoumean'] = $suggestedQuery;
        }
        
        $this->_afterSolrQuery($result);
        return $result;
    }
    
    public function queryPriceStatsAndCalculateGap()
    {
        $queryUrl = $this->_queryUrl;
        $rangeFields = $this->getRangeFields();
        if (is_array($rangeFields)) {
            $rangeFieldString = $this->_convertRangeFieldsToString();
        
            if (strpos($queryUrl, '?')) {
                $queryUrl .= '&'.$rangeFieldString;
            } else {
                $queryUrl .= '?'.$rangeFieldString;
            }
        }
        
        $args = $this->_arguments;
        $args['rows'] = 0;
        $args['facet'] = 'false';
        $result = $this->doGetRequest($queryUrl, $args);
        
        $gap = 0;
        
        if (isset($result['stats']['stats_fields']['price_decimal']['max'])) {
            $maxPrice = $result['stats']['stats_fields']['price_decimal']['max'];
            if ($maxPrice <= 100) {
                $gap = 10;
            } else if ($maxPrice > 100 && $maxPrice <= 200) {
                $gap = 20;
            } else if ($maxPrice > 200 && $maxPrice <= 300) {
                $gap = 30;
            } else if ($maxPrice > 300 && $maxPrice <= 400) {
                $gap = 40;
            } else if ($maxPrice > 400 && $maxPrice <= 500) {
                $gap = 50;
            } else if ($maxPrice > 500 && $maxPrice <= 1000) {
                $gap = 100;
            } else if ($maxPrice > 1000 && $maxPrice <= 10000) {
                $gap = 1000;
            } else if ($maxPrice > 10000 && $maxPrice <= 50000) {
                $gap = 5000;
            } else if ($maxPrice > 50000) {
                $gap = 10000;
            }
        }
        
        return $gap;
    }
    
    protected function _afterSolrQuery(&$result)
    {
        $result['allowMultipleFilterAttributes'] = $this->_multipleFilterAttributes;
        return $this;
    }
    
    static public function _getSuggestedSpellText($result)
    {
        $suggestedQueryText = null;
        if (isset($result['spellcheck']['collations']['collation']))
        {
            $suggestedQueryText = strtolower($result['spellcheck']['collations']['collation']);
        }
        return $suggestedQueryText;
    }
    
    static public function _resultFoundOk($result)
    {
        if (isset($result['response']['numFound']) && intval($result['response']['numFound']) > 0) {
            return true;
        }
        return false;
    }
    
    public function doGetRequest($requestUrl, $params = array())
    {
        if(isset($params['timestamp'])) {
            $params['timestamp'] = 0;
        }
        $cacheKey = $requestUrl.$this->serializer->serialize($params).'_'.$this->getStoreId();
        $cacheKey = sha1($cacheKey);
        
        $response = $this->cache->load($cacheKey);
        if ($response) {
            $response = $this->serializer->unserialize($response);
        } else {
            $response = parent::doGetRequest($requestUrl, $params);
            if ($this->_resultFoundOk($response)) {
                $this->cache->save($this->serializer->serialize($response), $cacheKey, ['solrbridge_search_query'], 3600);
            }
        }
        
        return $response;
    }
}