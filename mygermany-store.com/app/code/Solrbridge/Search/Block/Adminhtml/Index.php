<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Block\Adminhtml;

use Solrbridge\Search\Model\ResourceModel\Index as ResourceIndex;
use Solrbridge\Search\Model\ResourceModel\Index\Collection;
use Magento\Backend\Helper\Data;
use \Magento\Catalog\Model\ProductFactory;
use Solrbridge\Search\Model\Index as SolrbridgeIndex;

/**
 * Customer front  newsletter manage block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Index extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    protected $storeSwitcher;
    
    protected $messageManager;
    
    protected $errorMessages = array();
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Block\Store\Switcher $storeSwitcher,
        \Magento\Framework\Message\Manager $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeSwitcher = $storeSwitcher;
        $this->messageManager = $messageManager;
    }
    /**
     * Return the save action Url.
     *
     * @return string
     */

    public function getAction()
    {
        return $this->getUrl('newsletter/manage/save');
    }
    
    public function getReindexUrl($index)
    {
        return $this->getUrl('solrbridge/index/reindex', array('index_id' => $index->getId()));
    }
    
    public function getDeleteIndexUrl($index)
    {
        return $this->getUrl('solrbridge/index/delete', array('index_id' => $index->getId()));
    }

    public function getWebsites()
    {
        return $this->storeSwitcher->getWebsites();
    }

    public function getGroupCollection($website)
    {
        return $this->storeSwitcher->getGroupCollection($website);
    }

    public function getStoreCollection($group)
    {
        return $this->storeSwitcher->getStoreCollection($group);
    }

    public function getIndexCollection()
    {
        /*
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $collection = $productCollection->create()
                    ->addAttributeToSelect('*')
                    ->load();
        */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $indexModel = $objectManager->create('Solrbridge\Search\Model\Index');

        return $indexModel->getCollection();
    }
    
    protected function addError($exception)
    {
        $errorMessage = $exception->getMessage();
        $key = md5($errorMessage);
        //to prevent display duplicated error messages
        if (!in_array($key, $this->errorMessages)) {
            $this->errorMessages[] = $key;
            $this->messageManager->addError($errorMessage);
        }
        return $this;
    }

    public function getSolrCoreCollection()
    {
        $solrClient = new \Solrbridge\Solr\Library\Client();
        try {
            $cores = $solrClient->getAllAvailableSolrCores();
        } catch (\Exception $e) {
            //Add error message here to display to User
            $this->addError($e);
            $cores = array();
        }
        
        return $cores;
    }
    
    public function getDocumentCount($index)
    {
        $documentCount = 0;
        try {
            $documentCount = $index->getDocumentCount();
        } catch (\Exception $e) {
            $this->addError($e);
        }
        return $documentCount;
    }
    
    public function getPercentStatus($index)
    {
        $percent = 0;
        try {
            $percent = $index->getPercentStatus();
        } catch (\Exception $e) {
            $this->addError($e);
        }
        return $percent;
    }
    
    public function getDoctypes()
    {
        return SolrbridgeIndex::getDoctypes();
    }

    public function getProductCount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        $productVisibility = $objectManager->create('Magento\Catalog\Model\Product\Visibility');
        $this->productFactory = $productFactory;

        //$store = $this->_getStore();
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        );
        $collection->setVisibility($productVisibility->getVisibleInSiteIds());
        
        return $collection->getSize();
    }
}
