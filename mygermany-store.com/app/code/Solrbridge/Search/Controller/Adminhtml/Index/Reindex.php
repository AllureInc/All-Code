<?php
/**
 *
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Adminhtml\Index;

class Reindex extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_adminHelper = null;
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($indexId = $this->getRequest()->getParam('index_id')) {
            $this->_runReIndex($indexId);
            return;
        }
        $this->messageManager->addError(__('We can\'t find a index map to delete.'));
        $this->_redirect('index/index');
    }
    
    protected function getHelper()
    {
        if (null === $this->_adminHelper) {
            $this->_adminHelper = $this->_objectManager->create('\Magento\Backend\Helper\Data');
        }
        return $this->_adminHelper;
    }
    
    protected function _runReIndex($id)
    {
        try {
            /** @var \Magento\User\Model\User $model */
            $index = $this->_objectManager->create('Solrbridge\Search\Model\Index');
            $index->load($id);
            
            //If index id not found, show error and redirect to index management
            if ($id != $index->getId()) {
                $this->messageManager->addError(__('We can\'t find a index map to delete.'));
                $this->_redirect('index/index');
                return;
            }
            
            //prepare page number
            $page = $this->getRequest()->getParam('page');
            if (!$page) {
                $page = 1;
            }
            
            //If page is empty or page is 1 (first page), truncate index first
            if (1 >= $page) {
                $index->getDoctypeHandlerModel()->getIndexer()->truncate();
            }
            
            $indexer = $index->getDoctypeHandlerModel()->getIndexer();
            
            //Start reindexing
            $indexData = $index->getData();
            $request = array(
                    'solrcore' => $indexData['solr_core'],
                    'action' => $indexer::ACTION_RE_INDEX,
                    'starttime' => time(),
                    'count' => 0
            );
            $request = array_merge($request, $indexData);
            
            $request['page'] = $page;

            $indexer->start($request);

            $indexer->execute();
            
            $indexer->commit();

            $indexer->checkIndexStatus();

            $response = $indexer->end();
            
            $response['action_url'] = $this->getHelper()->getUrl('solrbridge/index/reindex', array('index_id' => $response['index_id']));
            
            if (isset($response['status']) && $response['status'] == $indexer::STATUS_FINISH) {
                $indexer->commit();
                $indexer->synchronizeData();
                //break;
            }
            
            $this->getResponse()->setHeader('Content-Type', 'application/json', true);
            $this->getResponse()->setBody(json_encode($response));
            
            
            /*
            $model->delete();
            $this->messageManager->addSuccess(__('You deleted the index map.'));
            $this->_redirect('index/index');
            */
            return $response;
        } catch (\Exception $e) {
            //echo $e->getMessage();
            //$this->messageManager->addError($e->getMessage().print_r($index->getData(), true));
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('index/index');
            return;
        }
    }
}
