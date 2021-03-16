<?php
/**
 *
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Adminhtml\Index;

class Save extends \Magento\Framework\App\Action\Action
{
    public function getStoreManager()
    {
        return $this->_objectManager->get('Magento\Store\Model\StoreManager');
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost('index');
        //1 - Validation data

        //2 - Save new Index
        $this->createNewIndexMap($postData);

        $this->_redirect('solrbridge/index/index');
        return;
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Solrbridge_Search::solrbridge_index_management');
        //$resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        //$resultPage->addBreadcrumb(__('Manage Pages'), __('Manage Pages'));
        $resultPage->getConfig()->getTitle()->prepend(__('Solrbridge Index Management'));

        //$this->saveSampleData();

        return $resultPage;
    }
    protected function createNewIndexMap($indexData)
    {
        if ($this->_validate($indexData)) {
            $model = $this->_objectManager->create('Solrbridge\Search\Model\Index');
            $model->setData($indexData);
            $model->setData('updated_at', time());
            $model->save();
        } else {
            $store = $this->getStoreManager()->getStore($indexData['store_id']);
            $message = __('The store <b>%1</b> has been mapped already. Store - Doctype - SolrCore must be unique. And 1 store allow to map with only 1 solrcore.', $store->getName());
            
            $this->messageManager->addError($message);
        }
    }

    /**
    * Validate index data before save
    */
    protected function _validate($indexData)
    {
        $indexCollection = $this->_objectManager
                ->create('Solrbridge\Search\Model\Index')
                ->getCollection();
        $indexCollection->addFieldToFilter('store_id', array('eq' => $indexData['store_id']));
        //1. If store Id not yet exists in table solrbridge_solrsearch_index, return true
        if ($indexCollection->getSize() < 1) {
            return true;//Valid
            //2. If Store Id exists in table solrbridge_solrsearch_index at least 1 record
        } else {
            $index = $indexCollection->getFirstItem();
            $solrCore = $index->getSolrCore();
            //2.1 - 1 store allow to map with 1 only solr core
            if ($indexData['solr_core'] != $solrCore) {
                return false;
            }

            $indexCollection = $this->_objectManager
                ->create('Solrbridge\Search\Model\Index')
                ->getCollection();
            $indexCollection->addFieldToFilter('store_id', array('eq' => $indexData['store_id']));
            $indexCollection->addFieldToFilter('doctype', array('eq' => $indexData['doctype']));
            if ($indexCollection->getSize() > 0) {
                return false;
            }
            return true;
        }
        return false;
    }
}
