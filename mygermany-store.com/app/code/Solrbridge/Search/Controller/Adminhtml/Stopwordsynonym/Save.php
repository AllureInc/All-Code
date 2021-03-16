<?php
/**
 *
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Adminhtml\Stopwordsynonym;

use Solrbridge_Library_Client as SolrClient;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_adminHelper = null;
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
    
    protected function getHelper()
    {
        if (null === $this->_adminHelper) {
            $this->_adminHelper = $this->_objectManager->create('\Magento\Backend\Helper\Data');
        }
        return $this->_adminHelper;
    }
    
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $solrConnection = new \Solrbridge\Solr\Library\Client\Admin();
        
        $solrcore = $this->getRequest()->getPost('solrcore');
        $synonyms = $this->getRequest()->getPost('synonyms', array());
        $stopwords = $this->getRequest()->getPost('stopwords', array());
        //convert all stopwords into lowercase
        foreach ($stopwords as $index => $stopword) {
            if (strlen($stopword) <= 0) {
                unset($stopwords[$index]);
                continue;
            }
            $stopwords[$index] = strtolower($stopword);
        }
        //convert all synonyms to lowercases
        $synonymData = array();
        if (isset($synonyms['term']) && isset($synonyms['synonym'])) {
            foreach ($synonyms['term'] as $index => $term) {
                if (isset($synonyms['synonym'][$index])) {
                    $synonymString = strtolower($synonyms['synonym'][$index]);
                    $synonymData[strtolower($term)] = @explode(',', $synonymString);
                    //Expand term into synonym
                    //$synonymData[strtolower($term)][] = $term;
                } else {
                    $synonymData[strtolower($term)] = array();
                }
            }
        }
    
        $redirectUrl = $this->getHelper()->getUrl('solrbridge/stopwordsynonym/index');
    
        if (!empty($solrcore)) {
            //Ping ok
        
            //Delete all synonyms
            $_synonyms = $solrConnection->getSynonyms($solrcore);
            foreach ($_synonyms as $_term => $_synonym) {
                $solrConnection->deleteSynonym($solrcore, $_term);
            }
            //Save synonym
            $synonyms = $solrConnection->saveSynonymBySolrCore($solrcore, $synonymData);
        
            //Delete all stopwords
            $_stopwords = $solrConnection->getStopwords($solrcore);
            foreach ($_stopwords as $_stopword) {
                if (!in_array($_stopword, $stopwords)) {
                    $solrConnection->deleteStopword($solrcore, $_stopword);
                }
            }
            //Save all new stopwords
            $stopwords = $solrConnection->saveStopWordBySolrCore($solrcore, $stopwords);
        
            //Reload solrcore to make stopwords and synonyms take effects
            $solrConnection->reloadSolrCore($solrcore);
        
            $params = array('solrcore' => $solrcore);
            $redirectUrl = $this->getHelper()->getUrl('solrbridge/stopwordsynonym/index', $params);
        }
        //Reload current page
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
