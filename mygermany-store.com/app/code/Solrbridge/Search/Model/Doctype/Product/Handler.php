<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\Doctype\Product;

use Solrbridge\Search\Model\Doctype\HandlerAbstract;
use Solrbridge\Search\Helper\Utility;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class Handler extends HandlerAbstract
{
    const CATEGORY_PATH_KEY = 'cat_path';
        
    protected $_fetchedRecords = 0;
    
    protected $_documents = '';
    
    protected $_documentData = array();
    
    protected $_attributeCollection = null;
    
    protected $_categoryManagement;
    
    protected $_categoryRepository;
    
    protected $_categoryResource;
    
    protected $_dataProductCategoryIds = array();
    protected $_dataProductCategoryPositions = array();
    protected $_dataProductAttributeValues = array();
    protected $_dataProductAttributeValuesOptions = array();
    protected $_dataProductAttributeValuesOptionsLabels = array();
    protected $_dataProductAttributeData = array();
    protected $_dataProductUrlRewrite = array();
    protected $_dataProductImages = array();
    protected $_attributeOptions = array();
    
    protected $_documentTextSearch = array();
    protected $_documentAutocompleteSearch = array();
    protected $_documentCategoryNames = array();
    
    protected $_autocompleteBrandAttrCode = null;
    
    protected $_loadedSynonyms = null;
    
    protected $_productAttrCodesUseForTermGeneration = array();
    
    protected $_productAttrCodeUseForSearchWeight = null;
    
    protected $_useProductCategoryForTermGeneration = null;
    
    protected $_helper = null;
    
    protected $indexerFactory;
    
    protected $tableMaintainer;
    
    protected $urlFinder;
    
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Model\CategoryManagement $categoryManagement,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer $tableMaintainer,
        \Solrbridge\Search\Helper\Data $helper,
        \Solrbridge\Search\Model\IndexerFactory $indexerFactory,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\UrlFactory $urlFactory
    ) {
        $this->_attributeCollection = $attributeCollection;
        $this->_categoryManagement = $categoryManagement;
        $this->_categoryResource = $categoryResource;
        $this->_categoryRepository = $categoryRepository;
        $this->_helper = $helper;
        $this->indexerFactory = $indexerFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->urlFinder = $urlFinder;
        $this->urlFactory = $urlFactory;
    }
    
    public function getIndexer()
    {
        $index = $this->getIndex();
        $indexer = $this->indexerFactory->create();
        $indexer->setIndex($index);
        return $indexer;
    }
    
    /**
     * Retrieve URL Instance
     *
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }
    
    /**
    * Define which product attribute codes will be used for term generation for autocomplete
    * @return array
    */
    protected function getProductAttrCodesUseForTermGeneration()
    {
        if (count($this->_productAttrCodesUseForTermGeneration) < 1) {
            $productAttributeCodes = $this->_helper->getAutocompleteSetting('termgeneration/product_attribute_codes');
            $productAttributeCodes = trim($productAttributeCodes, ',');
            $this->_productAttrCodesUseForTermGeneration = explode(',', $productAttributeCodes);
        }
        return $this->_productAttrCodesUseForTermGeneration;
    }
    
    /**
    * Define which product attribute used for search weight
    * @return array
    */
    protected function getProductAttrCodeUseForSearchWeight()
    {
        if (null === $this->_productAttrCodeUseForSearchWeight) {
            $this->_productAttrCodeUseForSearchWeight = $this->_helper->getGeneralSetting('advanced/product_search_weight');
        }
        return $this->_productAttrCodeUseForSearchWeight;
    }
    
    protected function useProductCategoryForTermGeneration()
    {
        if (null === $this->_useProductCategoryForTermGeneration) {
            $this->_useProductCategoryForTermGeneration = (boolean) $this->_helper->getAutocompleteSetting('termgeneration/use_product_category');
        }
        return $this->_useProductCategoryForTermGeneration;
    }
    
    public function getDataCollection($productIds = array())
    {
        $store = $this->getIndex()->getStore();
        //Return data collection class
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        
        $collection = $productFactory->create()->getCollection()->setStore($store);
        
        $collection->addAttributeToSelect(
            'name'
        );
        
        $productVisibility = $objectManager->create('Magento\Catalog\Model\Product\Visibility');
        
        if (is_array($productIds) && count($productIds) > 0) {
            $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        }
        
        $collection->setVisibility($productVisibility->getVisibleInSiteIds());
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        
        return $collection;
    }
    
    public function getDataCollectionForUpdate($collection)
    {
        //Return data collection class...ignore records exists in Index table
    }
    
    public function loadProductAttributeCollection()
    {
        $this->_attributeCollection->addToIndexFilter();
    }
    
    public function getProductAttributeCollection()
    {
        return $this->_attributeCollection;
    }
    
    public function loadProductUrls($productIds)
    {
        $filterData = [
            UrlRewrite::ENTITY_ID => $productIds,
            UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $this->getIndex()->getStoreId(),
        ];
        $urlItems = $this->urlFinder->findAllByData($filterData);
        $productUrls = [];
        foreach ($urlItems as $urlItem) {
            $productUrls[$urlItem->getEntityId()][] = $urlItem->getRequestPath();
            if (!isset($this->_dataProductUrlRewrite[$urlItem->getEntityId()])) {
                $this->_dataProductUrlRewrite[$urlItem->getEntityId()] = $urlItem->getRequestPath();
            }
        }
        return $this;
    }
    
    public function loadProductImages($productIds)
    {
        $connection = $this->_attributeCollection->getConnection();
        
        $galleryValueTable = $connection->getTableName('catalog_product_entity_media_gallery_value');
        $mediaGalleryTable = $connection->getTableName('catalog_product_entity_media_gallery');
        $select = $connection->select()
            ->from($galleryValueTable, '*')
            ->join(
                $mediaGalleryTable,
                $galleryValueTable.'.value_id = '.$mediaGalleryTable.'.value_id',
                array('image' => 'value')
            )
            ->where('entity_id IN (?)', $productIds);
                
        $result = $connection->query($select);
        while ($row = $result->fetch()) {
            $this->_dataProductImages[$row['entity_id']] = $row['image'];
        }
    }
    
    /**
    * Load attributes values for array $productIds
    */
    public function loadProductAttributeValues($productIds = array())
    {
        $selects = [];
        $storeId = $this->getIndex()->getStoreId();
        $storeIds = array(0, $storeId);
        
        $connection = $this->_attributeCollection->getConnection();
        
        $attributeTables = array();
        $attributesMap = array();
        
        $this->_dataProductAttributeValues = array();
        
        $attributesWithSelect = array();
        $attributesWithMultipleSelect = array();
        
        $productSearchWeightAttributes = $this->getProductAttrCodeUseForSearchWeight();
        if (!empty($productSearchWeightAttributes)) {
            $productSearchWeightAttributes = explode(',', $productSearchWeightAttributes);
            $conditions = 'main_table.attribute_code IN (?)';
            $this->_attributeCollection->getSelect()->orWhere($conditions, $productSearchWeightAttributes);
        }
        
        foreach ($this->_attributeCollection as $attribute) {
            $this->_dataProductAttributeData[$attribute->getAttributeCode()] = $attribute;
            $this->_dataProductAttributeData[$attribute->getAttributeId()] = $attribute;
            if (!$attribute->isStatic()) {
                //Collect attributes with frontendInput is select
                if ($attribute->getFrontendInput() == 'select') {
                    //Ignore attribute has source model, those attributes will load option from source model
                    $attributesWithSelect[$attribute->getAttributeId()] = $attribute->getBackend()->getTable();
                }
                //Collect attributes with frontendInput is multiple select
                if ($attribute->getFrontendInput() == 'multiselect') {
                    $attributesWithMultipleSelect[$attribute->getAttributeId()] = $attribute->getBackend()->getTable();
                }
                $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                $attributesMap[$attribute->getAttributeId()] = $attribute->getAttributeCode();
            }
        }
        
        $productEntityLinkField = $this->getProductEntityLinkField();
        
        $index = 1;
        if (count($attributeTables)) {
            $attributeTables = array_keys($attributeTables);
            foreach ($productIds as $productId) {
                foreach ($attributeTables as $attributeTable) {
                    $alias = 't'.$index;
                    $aliasEntity = 'tf'.$index;
                    $select = $connection->select()
                        ->from(
                            [$alias => $attributeTable],
                            [
                                'value' => $alias.'.value',
                                'attribute_id' => $alias.'.attribute_id'
                            ]
                        )->joinInner(
                            [$aliasEntity => 'catalog_product_entity'],
                            "{$alias}.{$productEntityLinkField} = {$aliasEntity}.{$productEntityLinkField}",
                            [
                                'product_id' => $aliasEntity.'.entity_id'
                            ]
                        )
                        ->where($aliasEntity.'.entity_id = ?', $productId)
                        ->where($alias.'.store_id' . ' IN (?)', $storeIds)
                        ->order($alias.'.store_id' . ' DESC');
                    $index++;
                    $selects[] = $select;
                }
            }
            
            //Please be careful here Because $unionSelect can be nothing that is the reason for fetchAll throw error
            if (count($selects) > 0) {
                $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                    $selects,
                    \Magento\Framework\DB\Select::SQL_UNION_ALL
                );
                
                foreach ($connection->fetchAll($unionSelect) as $attributeValue) {
                    if (array_key_exists($attributeValue['attribute_id'], $attributesMap)) {
                        $pId = $attributeValue['product_id'];
                        $attrId = $attributeValue['attribute_id'];
                        
                        $this->_dataProductAttributeValues[$pId][$attributesMap[$attrId]] = $attributeValue['value'];
                    
                        //Collect data for attribute has options like select/multiple select
                        if (in_array($attrId, array_keys($attributesWithSelect))) {
                            //This function call may cause performance issue - need better way
                            //load attribute option labels for autocomplete only, if laod attribute it may cause performance issue
                            if ($this->_isAttributeRequiredToLoadOptionLabel($attrId)) {
                                $attributeValue['option_label'] = $this->_getAttributeOptionLabels(
                                    $connection,
                                    $attrId,
                                    $attributeValue['value'],
                                    $storeIds
                                );
                            }
                            
                            $this->_dataProductAttributeValuesOptions[$pId][$attributesMap[$attrId]] = array($attributeValue['value']);
                        }
                        if (in_array($attrId, array_keys($attributesWithMultipleSelect))) {
                            $this->_dataProductAttributeValuesOptions[$pId][$attributesMap[$attrId]] = explode(',', $attributeValue['value']);
                            //This function call may cause performance issue - need better way
                            //load attribute option labels for autocomplete only, if laod attribute it may cause performance issue
                            if ($this->_isAttributeRequiredToLoadOptionLabel($attrId)) {
                                $attributeValue['option_label'] = $this->_getAttributeOptionLabels(
                                    $connection,
                                    $attrId,
                                    explode(',', $attributeValue['value']),
                                    $storeIds
                                );
                            }
                        }
                        //TODO: for multiple select
                        if (isset($attributeValue['option_label'])) {
                            $this->_dataProductAttributeValuesOptionsLabels[$pId][$attributesMap[$attrId]] = $attributeValue['option_label'];
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    protected function _isAttributeRequiredToLoadOptionLabel($attributeId)
    {
        $attribute = $this->_dataProductAttributeData[$attributeId];
        $condition1 = ($this->_getAutocompleteBrandAttributeCode() == $attribute->getAttributeCode());
        $condition2 = ($attribute->getData('solrbridge_search_boost_weight') > 0);
        return $condition1 || $condition2;
    }
    
    protected function _getAttributeOptionLabels($connection, $attributeId, $optionId, $storeIds)
    {
        $attributeObject = $this->_dataProductAttributeData[$attributeId];
        if ($attributeObject->getSourceModel()) {
            if (is_array($optionId)) {
                $optionTexts = array();
                foreach ($optionId as $_optionId) {
                    $optionText = $attributeObject->getSource()->getOptionText($optionId);
                    if (empty($optionText)) {
                        $optionText = $optionId;
                    }
                    //TODO: $optionText is Magento\Framework\Phrase
                    //convert to string is temporary solution
                    $optionTexts[$_optionId] = (string)$optionText;
                }
            } else {
                $optionText = $attributeObject->getSource()->getOptionText($optionId);
                if (empty($optionText)) {
                    $optionText = $optionId;
                }
                //TODO: $optionText is Magento\Framework\Phrase
                //convert to string is temporary solution
                return array($optionId => (string)$optionText);
            }
        }
        $optionTableName = $connection->getTableName('eav_attribute_option');
        $valueTableName = $connection->getTableName('eav_attribute_option_value');
        $select = $connection->select()->from(array('option_table' => $optionTableName), '*');
        $select->joinInner(
            array('option_value_table' => $valueTableName),
            '`option_table`.`option_id` = `option_value_table`.`option_id`',
            '*'
        );
        
        $select->where('option_value_table.option_id IN (?)', $optionId);
        $select->where('option_value_table.store_id IN (?)', $storeIds);
        $select->where('option_table.attribute_id = ?', $attributeId);
        $select->order('option_value_table.store_id' . ' ASC');
        
        $attributeOptions = $connection->fetchAll($select);
        
        //$value = $optionId;
        $value = array();
        foreach ($attributeOptions as $optionData) {
            if (isset($optionData['value']) && !empty($optionData['value'])) {
                if (is_array($value)) {
                    $value[$optionData['option_id']] = $optionData['value'];
                } else {
                    $value = array($optionData['option_id'] => $optionData['value']);
                }
            } else {
                $value[$optionData['option_id']] = $optionData['option_id'];
            }
        }
        return $value;
    }
    
    public function parseJsonData($collection, $options = array())
    {
        //$productIds = $collection->getAllIds();
        //sleep(5);
        $productIds = $collection->load()->getLoadedIds();
        
        $this->loadProductAttributeCollection();
        
        $this->loadProductUrls($productIds);
        
        $this->loadProductImages($productIds);
        
        try {
            $this->loadProductAttributeValues($productIds);
        } catch (\Exception $e) {
            $errorMessage = __('Error at File: %1, Function: %2', __FILE__, __FUNCTION__.'.');
            $errorMessage .= __('Origin error: %1', $e->getMessage());
            throw new \Exception($errorMessage);
        }
        
        $this->_loadCategoryIds($productIds);
        
        //$this->_loadCategoryData($productIds);
        /*
        echo 'ATTRIBUTES VALUES-------'.PHP_EOL;
        print_r($this->_dataProductAttributeValues);
        echo 'CATEGORIES IDS-------'.PHP_EOL;
        print_r($this->_dataProductCategoryIds);
        */
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $iterator = $objectManager->get('Magento\Framework\Model\ResourceModel\Iterator');
        //Parse json data for sending to Solr for indexing...
        $this->_documents = "{";
        //\Solrbridge\Search\Helper\Debug::log((string)$collection->getSelect());
        //sleep(5);
        //Walk collection
        $iterator->walk(
            $collection->getSelect(),
            array(array($this, 'prepareDocumentData')),
            $options
        );
        $jsonData = trim($this->_documents, ',').'}';
        
        return array('jsondata'=> $jsonData, 'fetchedRecords' => (int) $this->_fetchedRecords);
    }
    
    protected function _beforePrepareDocumentData()
    {
        //Reset data for each product
        $this->_documentData = array();
        $this->_documentTextSearch = array();
        $this->_documentCategoryNames = array();
        $this->_documentAutocompleteSearch = array();
    }
    
    /**
    * This function will create fields/data for each solr document
    */
    public function prepareDocumentData($args)
    {
        $this->_beforePrepareDocumentData();
        
        $data = $args['row'];
        $this->_documentData['document_id'] = $data['entity_id'];
        
        //Inject category index product position for sorting purpose
        $this->_documentData['cat_position_int'] = 0;
        if (isset($data['cat_index_position'])) {
            $this->_documentData['cat_position_int'] = $data['cat_index_position'];
        }
        
        $this->_injectDoctype();
        $this->_injectStoreId();
        $this->_injectWebsiteId();
        
        $this->_injectUniqueId();
        
        $this->_injectProductSkuIntoDocumentTextSearch($data);
            
        $this->_injectProductAttributeData($data['entity_id']);
        
        $this->_injectCategoryData($data['entity_id']);
        
        $this->_injectProductUrlData($data['entity_id']);
        
        $this->_injectProductImage($data['entity_id']);
        
        $this->_injectTextSearchData();
        
        $this->_injectAutocompleteSearchData();
        
        //print_r($this->_documentData);
        
        $this->_documents .= '"add": '.json_encode(array('doc'=>$this->_documentData)).",";
        
        //\Solrbridge\Search\Helper\Debug::log(print_r($this->_documentData, true).PHP_EOL);
        //$file = fopen('/tmp/solrbridge/'.$data['entity_id'].'.txt', 'w');
        //fwrite($file, print_r($this->_documentData, true));
        //fclose($file);
        
        $this->_fetchedRecords++;
        
        $this->_afterPrepareDocumentData();
    }
    
    protected function _afterPrepareDocumentData()
    {
        //To do some thing...
    }
    
    protected function _getAutocompleteBrandAttributeCode()
    {
        if (null == $this->_autocompleteBrandAttrCode) {
            $this->_autocompleteBrandAttrCode = $this->_helper->getAutocompleteBrandAttributeCode($this->getIndex()->getStore());
        }
        return $this->_autocompleteBrandAttrCode;
    }
    
    protected function _injectProductSkuIntoDocumentTextSearch($productData)
    {
        if (isset($productData['sku'])) {
            $this->_documentTextSearch[] = $productData['sku'];
            $this->_documentTextSearch[] = str_replace(array('-', '_'), '', $productData['sku']);
        }
    }
    
    protected function _injectProductAttributeData($productId)
    {
        $attributeCodesUsedForAutocomplete = $this->getProductAttrCodesUseForTermGeneration();
        $attributeUsedForBoost = array();
        $this->_documentData['document_search_weight_int'] = 0;
        //Load product attribute data for specific product
        if (isset($this->_dataProductAttributeValues[$productId])) {
            $attributeValues = $this->_dataProductAttributeValues[$productId];
            //Normal attributes
            foreach ($attributeValues as $attributeCode => $value) {
                $attribute = $this->_dataProductAttributeData[$attributeCode];
                $key = $attributeCode.'_'.$attribute->getBackendType();
                
                //Reduce files size in Solr index
                if ('text' !== $attribute->getBackendType()) {
                    $this->_documentData[$key] = $value;
                    if ($attribute->getIsFilterableInSearch() || $attribute->getIsFilterable()) {
                        $this->_documentData[$attributeCode.'_facet'] = $value;
                    }
                }
                
                if ('name' == $attributeCode) {
                    $attributeUsedForBoost[$attributeCode] = $value;
                } else {
                    if ($attribute->getData('solrbridge_search_boost_weight') > 0) {
                        $attributeUsedForBoost[$attributeCode] = $value;
                        if (isset($this->_dataProductAttributeValuesOptionsLabels[$productId][$attributeCode])) {
                            $labels = $this->_dataProductAttributeValuesOptionsLabels[$productId][$attributeCode];
                            if (!empty($labels)) {
                                $attributeUsedForBoost[$attributeCode] = $labels;
                            }
                        }
                    }
                }
                
                if ($attribute->getIsSearchable()) {
                    $this->_documentTextSearch[] = $value;
                }
                
                //put attribute values for autocomplete search
                if (is_array($attributeCodesUsedForAutocomplete) && in_array($attributeCode, $attributeCodesUsedForAutocomplete)) {
                    $this->_documentAutocompleteSearch[] = $value;
                }
                
                //Inject product search weight value
                if ($attributeCode == $this->getProductAttrCodeUseForSearchWeight()) {
                    $this->_documentData['document_search_weight_int'] = $value;
                }
            }
            //Attribute has options select/multiple select
            if (isset($this->_dataProductAttributeValuesOptions[$productId])) {
                $attributeRealValues = $this->_dataProductAttributeValuesOptions[$productId];
                foreach ($attributeRealValues as $attributeCode => $value) {
                    $attribute = $this->_dataProductAttributeData[$attributeCode];
                    $key = $attributeCode.'_facet';
                    $this->_documentData[$key] = $value;
                    //$this->_documentTextSearch[] = $value;
                }
            }
            //Inject data for autocomplete brand attribute
            if ($attrCode = $this->_getAutocompleteBrandAttributeCode()) {
                if (isset($this->_dataProductAttributeValuesOptionsLabels[$productId][$attrCode])) {
                    $_autocompleteBrands = $this->_dataProductAttributeValuesOptionsLabels[$productId][$attrCode];
                    if (!is_array($_autocompleteBrands)) {
                        $autocompleteBrands = array($_autocompleteBrands);
                    } else {
                        $autocompleteBrands = array();
                        foreach ($_autocompleteBrands as $k => $v) {
                            $autocompleteBrands[] = $v.'/'.$k;
                        }
                    }
                    $key = $attrCode.'_autocomplete_facet';
                    $this->_documentData[$key] = $autocompleteBrands;
                }
            }
        }
        //Inject attributes which used for solr boost
        if (is_array($attributeUsedForBoost) && count($attributeUsedForBoost) > 0) {
            foreach ($attributeUsedForBoost as $attributeCode => $value) {
                if (is_array($value)) {
                    $value = array_values($value);
                }
                $this->_documentData[$attributeCode.'_boost'] = $value;
                $this->_documentData[$attributeCode.'_boost_exact'] = $value;
                $this->_documentData[$attributeCode.'_relative_boost'] = $value;
            }
        }
    }
    
    /**
    * This function is not yet perfect...
    * need to find a way to speedup if site has a lot of categories
    */
    protected function _injectCategoryData($productId)
    {
        $store = $this->getIndex()->getStore();
        $rootCatId = $store->getRootCategoryId();
        $categoryTree = $this->_categoryManagement->getTree($rootCatId);
        
        $categoryPaths = array();
        $recusiveCategoryIds = array($categoryTree->getId());
        
        if (isset($this->_dataProductCategoryIds[$productId])) {
            $categoryIds = $this->_dataProductCategoryIds[$productId];
            foreach ($categoryIds as $categoryId) {
                $category = $this->_categoryRepository->get($categoryId, $store->getId());
                if ($category->getLevel() < 2) {
                    continue;
                }
                $categoryPathData = $this->getCategoryPath($categoryTree, $category, $store);
                $categoryPaths[] = $categoryPathData[self::CATEGORY_PATH_KEY];
                $_recusiveCategoryIds = $categoryPathData['category_ids'];
                $recusiveCategoryIds = array_merge($recusiveCategoryIds, $_recusiveCategoryIds);
                
                //Category product position
                $categoryPosition = 0;
                if (isset($this->_dataProductCategoryPositions[$productId][$categoryId])) {
                    $categoryPosition = $this->_dataProductCategoryPositions[$productId][$categoryId];
                }
                $this->_documentData['cat_'.$categoryId.'_position_int'] = $categoryPosition;
            }
        }
        
        $this->_documentData[self::CATEGORY_PATH_KEY] = $categoryPaths;
        //$this->_documentData['cat_facet'] = array_unique($recusiveCategoryIds);
        $this->_documentData['cat_facet'] = Utility::getUniqueArrayData($recusiveCategoryIds);
    }
    
    protected function _injectProductUrlData($productId)
    {
        if (isset($this->_dataProductUrlRewrite[$productId])) {
            $this->_documentData['url_varchar'] = $this->_dataProductUrlRewrite[$productId];
        } else {
            $this->_documentData['url_varchar'] = 'catalog/product/view/id/'.$productId;
        }
    }
    
    protected function _injectProductImage($productId)
    {
        if (isset($this->_dataProductImages[$productId])) {
            $this->_documentData['image_path_varchar'] = $this->_dataProductImages[$productId];
        } else {
            $this->_documentData['image_path_varchar'] = '';
        }
    }
    
    protected function _injectTextSearchData()
    {
        $this->_documentTextSearch = array_merge($this->_documentTextSearch, $this->_documentCategoryNames);
        //@TODO: Collect important text into textSearch, less important into textSearchText
        
        $this->_documentTextSearch = $this->_applySynonymForSearchData($this->_documentTextSearch);
        
        $this->_documentTextSearch = Utility::getUniqueArrayData($this->_documentTextSearch);
        
        //Prepare text search data
        $this->_documentData['textSearch'] = (array) $this->_documentTextSearch;
        $this->_documentData['textSearchText'] = (array) $this->_documentTextSearch;
        $this->_documentData['textSearchStandard'] = (array) $this->_documentTextSearch;
        $this->_documentData['textSearchGeneral'] = (array) $this->_documentTextSearch;
    }
    
    protected function _injectAutocompleteSearchData()
    {
        //Use product category for term generation
        if ($this->useProductCategoryForTermGeneration()) {
            $this->_documentAutocompleteSearch = array_merge($this->_documentAutocompleteSearch, $this->_documentCategoryNames);
        }
        
        $this->_documentAutocompleteSearch = $this->_applySynonymForSearchData($this->_documentAutocompleteSearch);
        $textAutocompleteSearch = Utility::getUniqueArrayData($this->_documentAutocompleteSearch);
        $this->_documentData['textAutocomplete'] = $textAutocompleteSearch;
        $this->_documentData['textAutocompleteAccents'] = $textAutocompleteSearch;
    }
    
    protected function getLoadedSynonyms()
    {
        if (null === $this->_loadedSynonyms) {
            $solrCore = $this->getIndex()->getSolrCore();
            $this->_loadedSynonyms = $this->getIndex()->getSolrConnection()->getSynonyms($solrCore);
        }
        return $this->_loadedSynonyms;
    }
    
    protected function _applySynonymForSearchData($textSearchDataArray = array())
    {
        $_synonyms = $this->getLoadedSynonyms();
        foreach ($_synonyms as $term => $synonyms) {
            $matches = preg_grep('/\b'.$term.'\b/iu', $textSearchDataArray);
            if (is_array($matches) && count($matches) > 0) {
                $textSearchDataArray = array_merge($textSearchDataArray, $synonyms);
            } else {
                foreach ($synonyms as $_subterm) {
                    $_matches = preg_grep('/\b'.$_subterm.'\b/iu', $textSearchDataArray);
                }
                
                if (is_array($_matches) && count($_matches) > 0) {
                    $textSearchDataArray = array_merge($textSearchDataArray, array($term));
                }
            }
        }
        return $textSearchDataArray;
    }
    
    /**
     * This is slow, but it was working well
     * Get category path
     * @param unknown_type $category
     */
    public function getCategoryPath($categoryTree, $category, $store)
    {
        $categoryPath = '';
        $categoryIds = array();
        if ($this->_checkCategoryVisibility($category)) {
            $this->_documentCategoryNames[] = $category->getName();
            $categoryPath = str_replace('/', '_._._', $category->getName());
            $categoryPath .= '/'.$category->getId().':'.$category->getData('position');
            $categoryIds = array($category->getId());
            //Loop to find parents
            while ($category->getLevel() > 1) {
                $parentCategory = $category->getParentCategory();
                $category = $this->_categoryRepository->get($parentCategory->getId(), $store->getId());
            
                if ($this->_checkCategoryVisibility($category)) {
                    $this->_documentCategoryNames[] = $category->getName();
                    $_categoryPath = str_replace('/', '_._._', $category->getName());
                    $isAnchor = ($category->getIsAnchor()) ? 1 : 0;
                    $_categoryPath .= '/'.$category->getId().':'.$category->getData('position').':'.$isAnchor;
                    $categoryPath = $_categoryPath.'/'.$categoryPath;
                    $categoryIds[] = $category->getId();
                }
            }
        }
        return array(self::CATEGORY_PATH_KEY => trim($categoryPath, '/'), 'category_ids' => $categoryIds);
    }
    
    protected function _checkCategoryVisibility($category)
    {
        //return $category && $category->getIsActive() && $category->getIncludeInMenu() > 0 && $category->getLevel() > 1
        return $category && $category->getIsActive() > 0 && $category->getLevel() > 1;
    }
    
    /**
    * Load all category ids for Products
    * @param array $productIds
    * @return Solrbridge\Search\Model\Doctype\Product\Handler
    */
    protected function _loadCategoryIds($productIds)
    {
        $tableName = $this->_categoryResource->getTable('catalog_category_product_index_replica');
        //$tableName = $this->tableMaintainer->getMainTable($this->getIndex()->getStoreId());
        
        $select = $this->_categoryResource->getConnection()->select()->distinct()->from(
            $tableName,
            ['product_id', 'category_id', 'position']
        )->where(
            'product_id IN(?) AND is_parent = 1',
            $productIds
        )->where(
            'store_id = ?',
            $this->getIndex()->getStoreId()
        )->where(
            'visibility != ?',
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
        );
        
        $rows = $this->_categoryResource->getConnection()->fetchAll($select);
        
        foreach ($rows as $productCategoryData) {
            $pId = $productCategoryData['product_id'];
            $this->_dataProductCategoryIds[$pId][] = $productCategoryData['category_id'];
            $position = $productCategoryData['position'];
            $this->_dataProductCategoryPositions[$pId][$productCategoryData['category_id']] = $position;
        }
        
        return $this;
    }
    
    protected function _injectDoctype()
    {
        $this->_documentData['document_type'] = $this->getIndex()->getDoctype();
    }
    
    protected function _injectStoreId()
    {
        $this->_documentData['store_id'] = $this->getIndex()->getStoreId();
    }
    protected function _injectWebsiteId()
    {
        $this->_documentData['website_id'] = $this->getIndex()->getStore()->getWebsiteId();
    }
    
    protected function _injectUniqueId()
    {
        if ($this->isDataEmpty($this->_documentData, 'document_type') ||
            $this->isDataEmpty($this->_documentData, 'document_id') ||
            $this->isDataEmpty($this->_documentData, 'website_id') ||
            $this->isDataEmpty($this->_documentData, 'store_id')
        ) {
            $errorMessage = __('Prepare unique_id failed at: %1, Function: %2', __FILE__, __FUNCTION__.'.');
            $errorMessage .= __('document_type, document_id, website_id, store_id must be not empty');
            throw new \Exception($errorMessage);
        }
        $uniqueId = $this->_documentData['document_type'];
        $uniqueId .= '_'.$this->_documentData['document_id'];
        $uniqueId .= '_'.$this->_documentData['website_id'];
        $uniqueId .= '_'.$this->_documentData['store_id'];
        $this->_documentData['unique_id'] = $uniqueId;
    }
}
