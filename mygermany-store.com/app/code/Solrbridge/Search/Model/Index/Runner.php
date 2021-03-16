<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\Index;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class Runner
{
    protected $_index = null;
    
    protected $_indexer = null;

    protected $_log = array();

    protected $_documentCount = 0;

    public function setIndex($index)
    {
        $this->_index = $index;
    }

    public function getIndex()
    {
        return $this->_index;
    }

    protected function validate()
    {
        $index = $this->getIndex();
        if (!$index) {
            throw new Exception('Index object not found at: '.__FILE__.':line:'.__LINE__);
        }
        $this->_indexer = $this->getIndex()->getIndexer();
    }

    protected function _getIndexer()
    {
        if (null === $this->_indexer) {
            $this->_indexer = $this->getIndex()->getDoctypeHandlerModel()->getIndexer();
        }
        return $this->_indexer;
    }

    public function runReIndex()
    {
        $this->validate();
        $index = $this->getIndex();
    
        $requestData = $index->getData();
        
        $indexer = $this->_getIndexer();
        
        $this->getIndex()->getDoctypeHandlerModel()->getIndexer()->truncate();
        
        $progress = 0;

        //Start reindexing the whole index
        $request = array(
                'solrcore' => $requestData['solr_core'],
                'action' => $indexer::ACTION_RE_INDEX,
                'starttime' => time(),
                'count' => 0,
        );
        $request = array_merge($request, $requestData);

        while (true) {
            try {
                $this->_indexer = $this->getIndex()->getDoctypeHandlerModel()->getIndexer();

                $this->_indexer->start($request);

                $this->_indexer->execute();

                $this->_indexer->checkIndexStatus();

                $response = $this->_indexer->end();
                
                $this->_indexer->commit();
            
                $indexer = $this->_indexer;

                if (isset($response['message']) && !empty($response['message'])) {
                    $messages = @implode("\n", $response['message'])."\n";
                    echo $messages;
                    $storeid = isset($response['currentstoreid']) ? $response['currentstoreid'] : 0;
                    $percent = isset($response['percent']) ? $response['percent'] : 0;
                    $this->getIndex()->getHandlerModel()->writeLog($messages, $storeid, $solrcore, $percent, true);
                }

                if (isset($response['status']) && $response['status'] == $indexer::STATUS_FINISH) {
                    //print_r($response);
                    $this->_indexer->commit();
                    $this->_indexer->synchronizeData();
                    break;
                }
                if (isset($response['status']) && $response['status'] == $indexer::STATUS_ERROR) {
                    break;
                }

                if (isset($response['status']) && $response['status'] == $indexer::STATUS_WAITING) {
                    $this->waiting($response);
                    break;
                }

                $request = $response;
                $request['action'] = $indexer::ACTION_RE_INDEX;
                if (isset($percent) && $percent > 100) {
                    $request['action'] = $indexer::ACTION_UPDATE_INDEX;
                }

                //$request['page'] = ($request['page'] + 1);
                unset($this->_indexer);
                
                if (\Solrbridge\Search\Helper\Debug::ENABLE) {
                    echo @implode(PHP_EOL, \Solrbridge\Search\Helper\Debug::getDebugData()).PHP_EOL;
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->getIndex()->getHandlerModel()->writeLog($e->getMessage(), 0, $solrcore, 100, true);
                break;
            }
        }
    }

    public function runUpdateIndex()
    {
        $this->validate();
        $index = $this->getIndex();
        $requestData = $index->getData();
    
        $this->_documentCount = (int) $this->getIndex()->getHandlerModel()->getDocumentCount();
    
        $indexer = $this->_indexer;

        $initAction = ($this->_documentCount > 0) ? $indexer::ACTION_UPDATEINDEX : $indexer::ACTION_REINDEX;

        $solrcore = $requestData['solr_core'];

        $request = array(
                'solrcore' => $solrcore,
                'action' => $initAction,
                'starttime' => time(),
        );
        $request = array_merge($request, $requestData);

        while (true) {
            try {
                $this->_indexer = $index->getIndexer();

                $this->_indexer->start($request);

                $this->_indexer->execute();

                $this->_indexer->checkIndexStatus();

                $response = $this->_indexer->end();
            
                $indexer = $this->_indexer;

                if (isset($response['message']) && !empty($response['message'])) {
                    $messages = @implode("\n", $response['message'])."\n";
                    echo $messages;
                    $storeid = isset($response['currentstoreid']) ? $response['currentstoreid'] : 0;
                    $percent = isset($response['percent']) ? $response['percent'] : 0;
                    $this->getIndex()->getHandlerModel()->writeLog($messages, $storeid, $solrcore, $percent, true);
                }

                if (isset($response['status']) && $response['status'] == $indexer::STATUS_FINISH) {
                    break;
                }
                if (isset($response['status']) && $response['status'] == $indexer::STATUS_ERROR) {
                    break;
                }
                $request = $response;
                unset($this->_indexer);
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->getIndex()->getHandlerModel()->writeLog($e->getMessage(), 0, $solrcore, 100, true);
                break;
            }
        }
    }

    public function runTruncate()
    {
        $this->validate();
        $index = $this->getIndex();
        $requestData = $index->getData();
    
        $indexer = $this->_indexer;
        $solrcore = $requestData['solr_core'];
        //get solr core
        $request = array(
                'solrcore' => $solrcore,
                'action' => $indexer::ACTION_TRUNCATE,
                'starttime' => time(),
        );
        $request = array_merge($request, $requestData);
    
        $this->_indexer->start($request);

        $this->_indexer->truncateIndex();

        $response = $this->_indexer->end();

        if (isset($response['message']) && !empty($response['message'])) {
            $messages = @implode("\n", $response['message']);
            echo $messages ."\n";
        }
    }
}
