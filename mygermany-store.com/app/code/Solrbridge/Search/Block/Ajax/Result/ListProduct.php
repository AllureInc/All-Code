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
namespace Solrbridge\Search\Block\Ajax\Result;

use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;
use Magento\Framework\View\Element\Template;

class ListProduct extends Template
{
    public function getJsonConfig()
    {
        $dataConfig = [
            'search_url' => $this->getRequest()->getUriString()
        ];
        
        return json_encode($dataConfig);
    }
}
