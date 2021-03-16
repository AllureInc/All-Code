<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model;

use \Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Area;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class Indexer
{
    const ACTION_RE_INDEX        = 'RE_INDEX';
    const ACTION_UPDATE_INDEX    = 'UPDATE_INDEX';
    const ACTION_TRUNCATE_INDEX  = 'TRUNCATE_INDEX';
    const ACTION_SYNCHRONIZE_INDEX  = 'SYNCHRONIZE_INDEX';
    
    const STATUS_START = 'START';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_CONTINUE   = 'CONTINUE';
    const STATUS_FINISH   = 'FINISH';
    
    static public $_allowedActions = array(
        self::ACTION_RE_INDEX,
        self::ACTION_UPDATE_INDEX,
        self::ACTION_TRUNCATE_INDEX,
        self::ACTION_SYNCHRONIZE_INDEX
    );
    
    static public $_allowedStatuses = array(
        self::STATUS_START,
        self::STATUS_PROCESSING,
        self::STATUS_CONTINUE,
        self::STATUS_FINISH
    );
    
    protected $index = null;
    
    protected $metaData = array();
    
    protected $page = null;
    
    protected $action = null;
    
    protected $response = array();
    
    protected $debug = true;
    
    protected $debugData = array();
    
    protected $dataIds = array();
    
    protected $emulation;
    
    protected function debug($message = '')
    {
        $this->debugData[] = '[DEBUG]'.$message.PHP_EOL;
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
    
    public function getLog()
    {
        return $this->response;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getIndexRunner()
    {
        \Solrbridge\Search\Helper\Debug::record();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $indexRunner = $objectManager->create('\Solrbridge\Search\Model\Index\Runner');
        $indexRunner->setIndex($this->getIndex());
        return $indexRunner;
    }
    
    /**
    * Constructor
    */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->emulation = $emulation;
    }
    
    public function startEmulation($storeId)
    {
        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
    }

    public function stopEmulation()
    {
        $this->emulation->stopEnvironmentEmulation();
    }
    
    protected function initPage($params)
    {
        \Solrbridge\Search\Helper\Debug::record();
        //Initialize page number for loading data collection purpose
        $this->page = 1;
        if (isset($params['page']) && $params['page'] > 0) {
            $this->page = $params['page'];
        }
    }
    
    protected function initAction($params)
    {
        \Solrbridge\Search\Helper\Debug::record();
        //Initialize action
        $this->action = self::ACTION_RE_INDEX;
        if (isset($params['action']) && in_array($params['action'], self::$_allowedActions)) {
            $this->action = $params['action'];
        }
        
        //Reindex - what cases?
        
        //UpdateIndex - What cases?
        
        //Truncate Index - What cases?
        
        return $this;
    }
    
    protected function initStatus($params)
    {
        \Solrbridge\Search\Helper\Debug::record();
        //Initialize status
        $this->_status = self::STATUS_START;
        if (isset($params['status']) && in_array($params['status'], self::$_allowedStatuses)) {
            $this->_status = $params['status'];
        }
        return $this;
    }
    
    protected function initLog($params)
    {
        \Solrbridge\Search\Helper\Debug::record();
        $this->response = array();
        return $this;
    }
    
    protected function initCollectionMetaData($params)
    {
        \Solrbridge\Search\Helper\Debug::record();
        $this->dataIds = array();
        if (isset($params['data_ids']) && is_array($params['data_ids']) && count($params['data_ids']) > 0) {
            $this->dataIds = $params['data_ids'];
        }
        $this->metaData = $this->getIndex()->getDoctypeHandlerModel()->getCollectionMetaData($this->dataIds);
    }
    
    /**
    * This is the entry point of Indexer
    */
    public function start($params = array())
    {
        \Solrbridge\Search\Helper\Debug::record();
        
        $this->initStatus($params);
        
        $this->initPage($params);
        
        $this->initLog($params);
        
        $this->initCollectionMetaData($params);
        
        $this->initAction($params);
        
        return $this;
    }
    
    /**
    * This is the processing function of Indexer
    */
    public function execute()
    {
        //$storeManager->setCurrentStore($this->getIndex()->getStore());
        
        //Clean solr index if dataIds found (this will be an action from Magento Admin)
        if (count($this->dataIds) > 0) {
            //delete solr documents
            $this->getIndex()->getSolrConnection()->deleteDocuments($this->dataIds);
        }
        
        //\Solrbridge\Search\Helper\Debug::log($oldStore->getId());
        $this->startEmulation($this->getIndex()->getStoreId());
        
        \Solrbridge\Search\Helper\Debug::record();
        if (self::ACTION_RE_INDEX == $this->action) {
            $this->runReindex();
        } else {
            $this->runUpdateIndex();
        }
        //$storeManager->setCurrentStore($oldStore);
        
        $this->stopEmulation();
        
        return $this;
    }
    
    protected function runReindex()
    {
        \Solrbridge\Search\Helper\Debug::record();
        //Fetching records from Magento Database
        $dataCollection = $this->metaData['collection'];

        $pageSize = $this->getIndex()->getDoctypeHandlerModel()->getItemPerCommit();
        $dataCollection->clear();
        
        //echo 'PAGE::::'.$this->page.PHP_EOL;
        //$dataCollection->getSelect()->limitPage(intval($this->page), $pageSize);
        $dataCollection->setPageSize($pageSize);
        $dataCollection->setCurPage(intval($this->page));
        //echo $this->page.PHP_EOL;
        //echo $dataCollection->getSelect().PHP_EOL;
        
        $options = array();
        $returnDataArray = $this->getIndex()->getDoctypeHandlerModel()->parseJsonData($dataCollection, $options);

        $jsonData = $returnDataArray['jsondata'];
        
        $postData = array('stream.body' => $jsonData);
        
        $this->getIndex()->getSolrConnection()->postJsonData($postData);
        //print_r($returnDataArray);
        
        //Continue next page or so?
    }
    
    protected function runUpdateIndex()
    {
        //do logic here
    }
    
    public function checkIndexStatus()
    {
        //doing logic here
        return $this;
    }
    
    protected function prepareResponseData()
    {
        \Solrbridge\Search\Helper\Debug::record();
        //prepare status and next page number
        if ($this->page >= $this->metaData['total_pages']) {
            //finished
            $this->response['status'] = self::STATUS_FINISH;
        } else {
            //going to next page
            $this->page++;
            $this->response['page'] = $this->page;
        }
        $this->response['index_id'] = $this->getIndex()->getId();
    }
    
    protected function prepareResponseMessage()
    {
        \Solrbridge\Search\Helper\Debug::record();
    }
    
    public function synchronizeData()
    {
        \Solrbridge\Search\Helper\Debug::record();
        return $this;
    }
    
    /**
    * Commit Solr Index
    */
    public function commit()
    {
        $this->getIndex()->getSolrConnection()->commit();
        return $this;
    }
    
    public function truncate()
    {
        $this->getIndex()->getSolrConnection()->truncate();
        return $this;
    }
    
    /**
    * This function return result after Indexer processing
    */
    public function end()
    {
        \Solrbridge\Search\Helper\Debug::record();
        $this->prepareResponseData();
        $this->prepareResponseMessage();
        return $this->getLog();
    }
}
