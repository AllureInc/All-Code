<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\Solr;

use Solrbridge\Solr\Library\Client as SolrbridgeSolrClient;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class Connection
{
    protected $solrClient = null;
    
    protected $index = null;
    
    public function construct(
        SolrbridgeSolrClient $solrClient
    ) {
        $this->solrClient = $solrClient;
    }
    
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
    
    protected function validate()
    {
        if (null === $this->solrClient) {
            throw new Exception('Solr client not found at '.__FILE__.':'.__LINE__);
        }
        if (null === $this->index) {
            throw new Exception('Index object not found at '.__FILE__.':'.__LINE__);
        }
        return $this;
    }
    
    public function ping()
    {
        $this->validate();
        $solrCore = $this->getIndex()->getSolrCore();
    }
}
