<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\SolrSearchCustom\Library\Solr\Client;

use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;

class Query extends \Solrbridge\Solr\Library\Client\Query
{
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
                    } elseif ($field == 'restricted_countries') {
                        $part = '';
                        foreach ($value as $val) {
                            $part .= '-filter('.$this->getFilterKey($field).':"'.urldecode($val).'") OR ';
                        }
                        $part = trim(trim($part), 'OR');
                        if ( !empty($part) ) {
                            $fq .= ' AND '.trim($part).'';
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

    protected function _prepareFacets()
    {
        $facetFields = $this->_getFacetFields();
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
        }

        // Extra facet added for country restriction.
        $facetFields[] = 'restricted_countries_facet';
        
        //inject category into facet fields
        $facetFields = array_merge(array(DoctypeHandler::CATEGORY_PATH_KEY), $facetFields);
        // print_r($this->_arguments);die;
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
}