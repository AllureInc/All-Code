<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\FskRestricted\Block;

use Solrbridge\Search\Helper\System as System;
/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
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
        if ($this->_productCollection === null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // ------------ ---------------- Restricted Product Working --------------------
            $newCollection = [];
            $secondCollectionsProductIds = [];
            $restrictedHelper = $objectManager->create('Mangoit\FskRestricted\Helper\Data');
            $currentCategory = $restrictedHelper->getCurrentCategory();
            $countryName = $restrictedHelper->getCurrentCountry();
            $productModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
            $productModelCollection = $productModel->getCollection()->addFieldToFilter('category_id',array('eq' => $currentCategory));
            foreach ($productModelCollection as $item) {
                $checkContryArr =  explode(",", $item['restricted_countries']);
                if (in_array($countryName, $checkContryArr)) {
                    array_push($secondCollectionsProductIds, $item['product_id']);
                }
            }
            // echo "<pre>";
            // print_r($secondCollectionsProductIds);
            // die();
            $collectionFactory = $objectManager->create('Solrbridge\Search\Model\ResourceModel\Product\CollectionFactory');
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
            $collection->addAttributeToFilter('entity_id', ['nin' => $secondCollectionsProductIds]);
            $collection->load();
            //  echo "<pre>";
            // print_r($collection->getData());
            // die();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}
