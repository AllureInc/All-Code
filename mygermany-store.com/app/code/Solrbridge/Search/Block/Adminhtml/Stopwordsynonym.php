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
use Solrbridge\Search\Helper\System;

/**
 * Customer front  newsletter manage block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Stopwordsynonym extends \Magento\Backend\Block\Template
{
    protected $storeSwitcher;
    
    protected $messageManager;
    
    protected $errorMessages = array();
    
    protected $solrConnection = null;
    
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
    
    public function getSolrConnection()
    {
        if (null === $this->solrConnection) {
            $this->solrConnection = new \Solrbridge\Solr\Library\Client\Admin();
        }
        return $this->solrConnection;
    }
    
    public function getSaveChangeUrl()
    {
        return $this->getUrl('solrbridge/stopwordsynonym/save');
    }
    
    public function getReloadUrl($solrcore)
    {
        return $this->getUrl('solrbridge/stopwordsynonym/index', array('solrcore' => $solrcore));
    }
    
    public function getFormKey()
    {
        $objectManager = System::getObjectManager();
        return $objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey();
    }
    
    public function getSelectedHtmlAttribute($solrcore = null)
    {
        $currentSolrCore = $this->getRequest()->getParam('solrcore');
        if (!empty($currentSolrCore) && !empty($solrcore) && $solrcore == $currentSolrCore) {
            return 'selected="selected"';
        }
        return '';
    }
    
    public function getCurrentSolrCore()
    {
        return $this->getRequest()->getParam('solrcore');
    }
    
    public function canShowForm()
    {
        $currentSolrCore = $this->getRequest()->getParam('solrcore');
        return !empty($currentSolrCore);
    }
    
    public function getStopwords()
    {
        $_stopwords = array();
        try {
            $_stopwords = $this->getSolrConnection()->getStopwords($this->getCurrentSolrCore());
        } catch (\Exception $e) {
            $this->addError($e);
        }
        return $_stopwords;
    }
    
    public function getSynonyms()
    {
        $returnSynonyms = array();
        try {
            $_synonyms = $this->getSolrConnection()->getSynonyms($this->getCurrentSolrCore());
            foreach ($_synonyms as $term => $synonyms) {
                $returnSynonyms[] = array('term' => $term, 'synonyms' => @implode(',', $synonyms));
            }
        } catch (\Exception $e) {
            $this->addError($e);
        }
        
        return $returnSynonyms;
    }
    
    public function getJsConfig()
    {
        $config = array(
            'synonyms' => $this->getSynonyms(),
            'stopwords' => $this->getStopwords()
        );
        return json_encode($config);
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
}
