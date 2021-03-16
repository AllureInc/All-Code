<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\FskRestricted\Block;


/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends \Solrbridge\Search\Block\Result\ListProduct
{
    protected $_solrSearchResultData = null;
    
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
    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        die("override");
        if ($this->_productCollection === null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collectionFactory = $objectManager->create('Solrbridge\Search\Model\ResourceModel\Product\CollectionFactory');
            //$this->_productFactory = $productFactory;

            //$store = $this->_getStore();
            $collection = $collectionFactory->create()->addAttributeToSelect('*');
            
            $result = $this->_getSolrSearchResultData();
            $foundProductIds = $result['productids'];
            $totalRecords = $result['recordcount'];
            
            $collection->setTotalRecords($totalRecords);
            $collection->setCurPage(1);
            if (count($foundProductIds)) {
                $collection->addFieldToFilter('entity_id', array('in' => $foundProductIds));
                $collection->getSelect()->order("find_in_set(e.entity_id,'" . implode(',', $foundProductIds) . "')");
            } else {
                $collection->addFieldToFilter('entity_id', array('in' => array(-1)));
            }
            
            $collection->load();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}
