<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\Doctype;

use Magento\Framework\App\ObjectManager;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class HandlerAbstract
{
    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;
    
    /**
     * Product entity link field
     *
     * @var string
     */
    protected $productEntityLinkField;
    
    protected $_itemsPerCommit = null;
    //Solrbridge_Search_Model_Index
    protected $_index = null;

    public function setIndex($index)
    {
        $this->_index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->_index;
    }

    public function getTableName()
    {
        //Return index table name ex: solrbridge_search_index_[index_id]
    }
    
    public function getItemPerCommit()
    {
        if (null == $this->_itemsPerCommit) {
            $this->_itemsPerCommit = $this->getIndex()->getHelper()->getGeneralSetting('indexer/item_per_commit');
        }
        return $this->_itemsPerCommit;
    }

    public function getDoctype()
    {
        $index = $this->getIndex();
        if ($index && $index->getDoctype() > 0) {
            return $index->getDoctype();
        }
        return 0;
    }
    
    public function getDataCollectionByStoreId($storeId)
    {
        //Implement this function to return Data Collection
    }
    
    public function getDataCollectionForUpdate($collection)
    {
        //Return data collection for update;
    }

    public function getCollectionMetaDataForUpdate($collection)
    {
        $collection = $this->getDataCollectionForUpdate($collection);

        $sql = $collection->getSelectCountSql();

        $recordCount = $collection->getConnection()->fetchOne($sql);

        $metaDataArray = array();
        //$productCount = $collection->getSize();
        $metaDataArray['recordCount'] = $recordCount;
        $totalPages = ceil($recordCount / $this->_itemsPerCommit);
        $metaDataArray['totalPages'] = $totalPages;
        $metaDataArray['collection'] = $collection;

        if ($this->writeLog) {
            $this->writeLog(print_r($metaDataArray, true));
        }

        return $metaDataArray;
    }
    
    /**
    * This function will collect main informations like
    * 1. total records count
    * 2. loadedStores
    * 3. loadedStoresName
    * 4. stores[storeid][recordcount]
    * 5. stores[storeid][totalpages]
    * 6. stores[storeid][collection] Varien_Data_Collection
    */
    public function getCollectionMetaData($dataIds = array())
    {
        $index = $this->getIndex();
        $storeId = $index->getStoreId();
        
        $metaDataArray = array();
        
        $dataCollection = $this->getDataCollection($dataIds);
        $dataCollection->setPageSize($this->getItemPerCommit());
        
        $recordsCount = $dataCollection->getSize();
        //$totalRecordsCount += $storeRecordsCount;
        $metaDataArray['record_count'] = $dataCollection->getSize();
        //$totalPages = ceil($recordsCount / $this->_itemsPerCommit);
        $metaDataArray['total_pages'] = $dataCollection->getLastPageNumber();
        $metaDataArray['collection'] = $dataCollection;
        $metaDataArray['store'] = $index->getStore();
        
        return $metaDataArray;
    }
    /**
    * This function return Doctype record count from Magento
    */
    public function getRecordCount()
    {
        $collectionData = $this->getCollectionMetaData();
        return $collectionData['record_count'];
    }

    public function getIndexer()
    {
        return Mage::getResourceModel('solrsearch/indexer');
    }

    /**
    * This function return Doctype document count from Solr
    */
    public function getDocumentCount()
    {
        $index = $this->getIndex();
        $solrcore = $index->getSolrCore();
        $storeId = $index->getStoreId();
        $doctype = $index->getDoctype();
        $filter = array('store_id' => $storeId, 'document_type' => $doctype);
        return $this->getIndex()->getSolrConnection()->getDocumentCount($filter);
    }
    
    public function getPercentStatus()
    {
        $documentCount = $this->getDocumentCount();
        $recordCount = $this->getRecordCount();
        return \Solrbridge\Search\Helper\Index::calculatePercent($recordCount, $documentCount);
    }

    public function getTotalDocuments()
    {
        return $this->getDocumentCount();
    }
    
    public function getAllDocuments()
    {
        $index = $this->getIndex();
        return Mage::getResourceSingleton('solrsearch/solr')->getAllDocumentByIndex($index);
    }
    public function truncateIndexTable()
    {
        $index = $this->getIndex();
        return Mage::getResourceSingleton('solrsearch/solr')->truncateIndexTable($index->getIndexId());
    }
    
    public function isDataEmpty($document, $field)
    {
        //Field not exist in array
        if (!isset($document[$field])) {
            return true;
        }
        //Field exists but value is empty
        if (empty($document[$field])) {
            return true;
        }
        return false;
    }
    
    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 100.1.0
     */
    protected function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
    
    /**
     * Get product entity link field
     *
     * @return string
     * @throws \Exception
     */
    protected function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
