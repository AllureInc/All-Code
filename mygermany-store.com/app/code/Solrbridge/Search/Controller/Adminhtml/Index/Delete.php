<?php
/**
 *
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Adminhtml\Index;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $response = array(
            'status' => 'ERROR',
            'message' => __('We can\'t find a index map to delete.')
        );
        
        if ($indexId = $this->getRequest()->getParam('index_id')) {
            $response = $this->deleteIndexMap($indexId);
        }
        //$this->messageManager->addError(__('We can\'t find a index map to delete.'));
        //$this->_redirect('index/index');
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($response));
    }
    protected function deleteIndexMap($id)
    {
        $response = array(
            'status' => 'SUCCESS',
            'message' => __('You deleted the index map.')
        );
        try {
            /** @var \Magento\User\Model\User $model */
            $index = $this->_objectManager->create('Solrbridge\Search\Model\Index');
            $index->load($id);
            //This should be truncate by doc_type
            $index->getSolrConnection()->truncate();
            $index->delete();
            //$this->messageManager->addSuccess(__('You deleted the index map.'));
            //$this->_redirect('index/index');
            //return;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            //$this->_redirect('index/index');
            //return;
            $response = array(
                'status' => 'SUCCESS',
                'message' => $e->getMessage()
            );
        }
        return $response;
    }
}
