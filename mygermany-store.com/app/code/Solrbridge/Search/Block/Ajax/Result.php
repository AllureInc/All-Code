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
namespace Solrbridge\Search\Block\Ajax;

use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;
use Magento\Framework\View\Element\Template;

class Result extends \Solrbridge\Search\Block\Result
{
    protected function _requestSolrSearch()
    {
        $solrSearchResult = parent::_requestSolrSearch();
        
        $searchResultCount = isset($solrSearchResult['recordcount']) ? $solrSearchResult['recordcount'] : 0;
        $this->setData('result_count', $searchResultCount);
        
        return $solrSearchResult;
    }
}
