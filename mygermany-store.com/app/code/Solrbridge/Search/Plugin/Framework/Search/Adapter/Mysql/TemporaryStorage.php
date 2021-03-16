<?php

namespace Solrbridge\Search\Plugin\Framework\Search\Adapter\Mysql;

use Magento\Framework\Api\Search\DocumentInterface;
use Solrbridge\Search\Helper\System as System;

class TemporaryStorage
{
    protected $_solrSearchResultData = null;
    
    protected $documentFactory;
    
    protected $attributeValueFactory;
    
    protected $registry;
    
    public function __construct(
        \Magento\Framework\Api\Search\DocumentFactory $documentFactory,
        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->documentFactory = $documentFactory;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->registry = $registry;
    }
    
    public function setSolrSearchResultData($data)
    {
        $this->_solrSearchResultData = $data;
        return $this;
    }
    
    public function _getSolrSearchResultData()
    {
        if (null === $this->_solrSearchResultData) {
            $this->_solrSearchResultData = System::getRegistry()->registry('solrbridge_search_result_data');
        }
        return $this->_solrSearchResultData;
    }
    
    public function beforeStoreApiDocuments($subject, $documents)
    {
        $searchResult = $this->registry->registry('solrbridge_search_result');
        if (!$searchResult) {
            return [$documents];
        }
        $documents = [];
        
        $result = $this->_getSolrSearchResultData();
        
        $foundProductIds = isset($result['productids']) ? $result['productids']: [];
        
        if (is_array($foundProductIds) && count($foundProductIds) > 0) {
            $foundProductIds = array_reverse($foundProductIds);
            foreach ($foundProductIds as $score => $productId) {
                $document = $this->documentFactory->create(['data' => ['id' => $productId]]);
                $documentScore = $this->attributeValueFactory->create();
                $documentScore->setValue($score);
                $document->setCustomAttribute('score', $documentScore);
                $documents[] = $document;
            }
        }
        
        return [$documents];
    }
}