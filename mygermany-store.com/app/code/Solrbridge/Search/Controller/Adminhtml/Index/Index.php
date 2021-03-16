<?php
/**
 *
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Adminhtml\Index;

use Solrbridge_Library_Client as SolrClient;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //$this->_testSolr();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Solrbridge_Search::solrbridge_index_management');
        //$resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        //$resultPage->addBreadcrumb(__('Manage Pages'), __('Manage Pages'));
        $resultPage->getConfig()->getTitle()->prepend(__('Solrbridge Index Management'));

        //$this->saveSampleData();

        return $resultPage;
    }

    protected function _testSolr()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $autoLoad = $objectManager->get('\Magento\Framework\Component\ComponentRegistrar');


        $paths = $autoLoad->getPaths(
            \Magento\Framework\Component\ComponentRegistrar::LIBRARY
        );
    }

    protected function saveSampleData()
    {
        $model = $this->_objectManager->create('Solrbridge\Search\Model\Index');
        $model->setData('store_id', 3);
        $model->setData('solr_core', 'english');
        $model->setData('title', 'Hello');
        $model->setData('updated_at', time());
        $model->save();
    }
}
