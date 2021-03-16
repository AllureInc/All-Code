<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Solrbridge\Solr\Library\Client;
use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;
use Solrbridge\Search\Helper\System;

class Admin extends \Solrbridge\Solr\Library\Client
{
    public function reloadSolrCore($solrcore) {
        $solrServerUrl =$this->getSolrServerUrl();
        $requestUrl = $this->buildUrl('/admin/cores');
        $this->doGetRequest($requestUrl, array('action' => 'RELOAD', 'core' => $solrcore));
    }
    
    public function saveStopWordBySolrCore($solrcore, $stopwords = array()) {
        $requestUrl = $this->buildUrl('/schema/analysis/stopwords/'.$solrcore, $solrcore);
        $response = $this->putRequest($requestUrl, $stopwords);
        return $response;
    }
    
    public function getStopwords($solrcore) {
        if (!empty($solrcore)) {
            $requestUrl = $this->buildUrl('/schema/analysis/stopwords/'.$solrcore, $solrcore);
            $response = $this->doRequest($requestUrl);
            $stopwords = array();
            if(isset($response['wordSet']['managedList'])) {
                $stopwords = $response['wordSet']['managedList'];
            }
            return $stopwords;
        }
        return array();
    }
    
    public function deleteStopword($solrcore, $stopword) {
        $requestUrl = $this->buildUrl('/schema/analysis/stopwords/'.$solrcore.'/'.$stopword, $solrcore);
        $this->deleteRequest($requestUrl);
    }
    
    public function saveSynonymBySolrCore($solrcore, $synonyms = array()) {
        $requestUrl = $this->buildUrl('/schema/analysis/synonyms/'.$solrcore, $solrcore);
        $response = $this->putRequest($requestUrl, $synonyms);
        return $response;
    }
    
    public function deleteSynonym($solrcore, $term) {
        $requestUrl = $this->buildUrl('/schema/analysis/synonyms/'.$solrcore.'/'.urlencode($term), $solrcore);
        $this->deleteRequest($requestUrl);
    }
}