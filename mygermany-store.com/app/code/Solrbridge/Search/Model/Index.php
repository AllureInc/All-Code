<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model;

use Magento\Framework\Model\AbstractModel;
use Solrbridge\Search\Helper\System;

class Index extends AbstractModel
{
    const DOCTYPE_PRODUCT   = 10;
    const DOCTYPE_CMS       = 20;
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'solrbridge_search_index';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    /**
     * @var \Magento\Store\Model\StoresConfig
     */
    protected $_storesConfig;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $classModelFactory;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoresConfig $storesConfig
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Tax\Model\ClassModelFactory $classModelFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoresConfig $storesConfig,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\ClassModelFactory $classModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storesConfig = $storesConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->classModelFactory = $classModelFactory;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Solrbridge\Search\Model\ResourceModel\Index');
    }
    
    public function getSolrConnection()
    {
        $connection = new \Solrbridge\Solr\Library\Client\Admin();
        $connection->setIndex($this);
        return $connection;
    }
    
    public function getHelper()
    {
        return System::getObjectManager()->get('Solrbridge\Search\Helper\Data');
    }
    
    public static function getDoctypes()
    {
        return array(
            self::DOCTYPE_PRODUCT   => __('Product'),
            //self::DOCTYPE_CMS       => __('Cms Page')
        );
    }
    
    public function getStore($storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        
        return $this->storeManager->getStore($storeId);
    }
    
    public function getDoctypeLabel()
    {
        $docType = $this->getDoctype();
        $docTypes = self::getDoctypes();
        if (isset($docTypes[$docType])) {
            return $docTypes[$docType];
        }
        return $docType;
    }
    
    public function getStoreHierachy()
    {
        $store = $this->getStore();
        $storeGroup = $store->getGroup();
        $website = $store->getWebsite();
        $styles = 'style="list-style-type:none;padding-left:15px"';
        $html = '
            <ul style="list-style-type:none">
                <li>
                    <a href="#">'.$website->getName().'</a>
                    <ul '.$styles.'>
                        <li>
                            <a href="#">'.$storeGroup->getName().'</a>
                            <ul '.$styles.'>
                                <li>
                                    <a href="#">'.$store->getName().'</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        ';
        return $html;
    }
    
    public function getDoctypeHandlerModel()
    {
        $objectManager = System::getObjectManager();
        $doctype = $this->getDoctype();
        if (self::DOCTYPE_PRODUCT == $doctype) {
            $handler = $objectManager->create('\Solrbridge\Search\Model\Doctype\Product\Handler');
            $handler->setIndex($this);
            return $handler;
        } elseif (self::DOCTYPE_CMS == $doctype) {
            $handler = $objectManager->create('\Solrbridge\Search\Model\Doctype\Cms\Handler');
            $handler->setIndex($this);
            return $handler;
        }
        throw new \Exception('Undefine doctype...at '.__FILE__.':'.__LINE__);
    }
    
    public function getRecordCount()
    {
        return $this->getDoctypeHandlerModel()->getRecordCount();
    }
    
    public function getDocumentCount()
    {
        return $this->getDoctypeHandlerModel()->getDocumentCount();
    }
    
    public function getPercentStatus()
    {
        return $this->getDoctypeHandlerModel()->getPercentStatus();
    }
}
