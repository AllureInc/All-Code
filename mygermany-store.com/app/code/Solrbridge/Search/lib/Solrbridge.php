<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\lib;

use Magento\Framework\App\Bootstrap;

final class Solrbridge
{
    static public $_config = null;
    
    static public $_storeId = 0;
    
    static public $_keywordSuggestionLimit = 5;
    static public $_productSuggestionLimit = 5;
    static public $_categorySuggestionLimit = 5;
    
    static public $_queryText = null;
    static public $_didYouMean = null;
    
    public static function hightlight($words, $text)
    {
        $newtext = $text;

        $split_words = explode(" ", trim($words));

        $specialChars = array('/', '\\', '*', '.', ')', '(');

        foreach ($split_words as $word) {
            $word = trim($word);
            if (!in_array($word, $specialChars)) {
                $text = preg_replace("/($word)(?=[^>]*(<|$))/ui", "<strong>$1</strong>", $text);
            }
        }
        
        if (!empty($text) && $text != null) {
            return $text;
        }
        
        return $newtext;
    }
    
    public static function loadMage()
    {
        $bootstrapFilePath = MAGENTO2_ROOT . FS . 'app' . FS . 'bootstrap.php';
        require_once $bootstrapFilePath;
        $params = $_SERVER;
        $bootstrap = Bootstrap::create(BP, $params);
        //Initialize Object Manager
        $bootstrap->getObjectManager();
    }
    
    public static function getConfig($path)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mageConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        return $mageConfig->getValue($path);
    }
    
    public static function getCurrencyConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore(self::$_storeId)->getCurrentCurrencyCode();
        $currency = $objectManager->create('Magento\Directory\Model\Currency')->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        return array('currency' => array('code' => $currencyCode, 'symbol' => $currencySymbol));
    }
    
    public static function getTopLevelCategories()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperData = $objectManager->get('Solrbridge\Search\Helper\Data');
        return array('categories' => $helperData->getTopLevelCategories());
    }
    
    public static function getTopTenPopularSearchTermsCachedResult($storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $queryCollection = $objectManager->get('Magento\Search\Model\ResourceModel\Query\Collection');
        $queryCollection->setOrder('popularity', 'DESC');
        $queryCollection->setPageSize(10);
        
        $topPopularity = [];
        foreach ($queryCollection as $query) {
            $topPopularity[] = $query->getQueryText();
        }
        
        $queryCollection->clear();
        $queryCollection->setOrder('updated_at', 'DESC');
        $queryCollection->setPageSize(5);
        
        foreach ($queryCollection as $query) {
            $topPopularity[] = $query->getQueryText();
        }
        
        $topPopularity = array_unique($topPopularity);
        $topPopularityCachedResults = [];
        
        foreach ($topPopularity as $term) {
            $result = self::query($term, $storeId, time());
            $topPopularityCachedResults[$term] = $result;
        }
        
        return array('topPopularCachedResult' => $topPopularityCachedResults);
    }
    
    public static function prepareConfig($storeId)
    {
        self::$_storeId = $storeId;
        
        $configFilePath = self::getConfigFilePath($storeId);

        if (!file_exists($configFilePath)) {
            $solrbridgeConfigData = array();
            $solrbridgeConfigData['solrbridge_general'] = self::getConfig('solrbridge_general');
            $solrbridgeConfigData['solrbridge_autocomplete'] = self::getConfig('solrbridge_autocomplete');
            
            //merge currency data
            $solrbridgeConfigData = array_merge($solrbridgeConfigData, self::getCurrencyConfig());
            $solrbridgeConfigData = array_merge($solrbridgeConfigData, self::getTopLevelCategories());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            //load solrbridge indexes
            $indexModel = $objectManager->create('Solrbridge\Search\Model\Index');
            $indexModel->load(self::$_storeId, 'store_id');
            
            //Load facet fields if advanced mode seleted
            $attributeCollection = $indexModel->getDoctypeHandlerModel()->getProductAttributeCollection();
            $attributeCollection->addIsFilterableInSearchFilter();
            $facetFields = array();
            foreach ($attributeCollection as $attribute) {
                $attrAllOptions = $attribute->setStoreId($storeId)->getSource()->getAllOptions();
                $allOptions = array();
                foreach ($attrAllOptions as $optionData) {
                    if (isset($optionData['value']) && !empty($optionData['value'])) {
                        $allOptions[$optionData['value']] = $optionData['label'];
                    }
                }
                
                $facetFields[$attribute->getAttributeCode()] = array(
                    'frontend_label' => $attribute->getFrontendLabel(),
                    'store_label' => $attribute->getStoreLabel(),
                    'options' => $allOptions,
                    'items' => array()
                );
            }
            $solrbridgeConfigData['solrbridge_layer_nav']['filterable_attributes'] = $facetFields;
            
            if ($indexModel && $indexModel->getData('store_id') == self::$_storeId) {
                $solrbridgeConfigData['solrbridge_general']['solr']['core'] = $indexModel->getData('solr_core');
            }
            
            //Decrypt password
            if (isset($solrbridgeConfigData['solrbridge_general']['solr']['server_authentication_password']) &&
                !empty($solrbridgeConfigData['solrbridge_general']['solr']['server_authentication_password'])
            ) {
                $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
                $decryptedPassword = $encryptor->decrypt($solrbridgeConfigData['solrbridge_general']['solr']['server_authentication_password']);
                $solrbridgeConfigData['solrbridge_general']['solr']['server_authentication_password'] = $decryptedPassword;
            }
            
            //check and create cache folder for solrbridge config files
            \Solrbridge\Search\Helper\Utility::getSolrBridgeCachePath();
            $configFile = fopen($configFilePath, 'w');
            //fwrite($configFile, serialize($solrbridgeConfigData));
            self::$_config = $solrbridgeConfigData;
            
            
            $solrbridgeConfigData = array_merge($solrbridgeConfigData, self::getTopTenPopularSearchTermsCachedResult($storeId));
            //echo serialize($solrbridgeConfigData);exit();
            fwrite($configFile, serialize($solrbridgeConfigData));
            
            fclose($configFile);
        }
    }
    
    public static function getConfigFilePath($storeId)
    {
        return MAGENTO2_ROOT.FS.'var'.FS.'cache'.FS.'solrbridge'.FS.'solrbridgesearch-store'.$storeId.'.json';
    }
    
    public static function hasConfigFile($storeId)
    {
        $configFilePath = self::getConfigFilePath($storeId);
        if (file_exists($configFilePath)) {
            //check timestamp cache 1 hour
            $hour = (time() - filemtime($configFilePath)) / 60 / 60;
            
            if ($hour > 1) {
                //remove file
                @unlink($configFilePath);
                return false;
            }
            return true;
        }
        
        return false;
    }
    
    public static function getKeywordSuggestionLimit()
    {
        $limit = self::getConfigData('solrbridge_autocomplete/autocomplete/keyword_suggestion_limit');
        if (is_numeric($limit)) {
            self::$_keywordSuggestionLimit = $limit;
        }
        return self::$_keywordSuggestionLimit;
    }
    
    public static function enableShowKeywordSuggestion()
    {
        return (boolean) self::getConfigData('solrbridge_autocomplete/autocomplete/show_keyword_suggestion');
    }
    
    private static function _getFacetPrefixUrl($queryText)
    {
        $queryUrl = '';
        $queryArray = explode(' ', $queryText);
        $tempQueryArray = array();
        foreach ($queryArray as $word) {
            $queryUrl .= '&f.textAutocomplete.facet.prefix='.urlencode(strtolower(trim($word)));
            $queryUrl .= '&f.textAutocompleteAccents.facet.prefix='.urlencode(strtolower(trim($word)));

            if (count($tempQueryArray) > 0) {
                $tempQueryArray[] = $word;
                $queryUrl .= '&f.textAutocomplete.facet.prefix='.urlencode(strtolower(trim(@implode('+', $tempQueryArray))));
                $queryUrl .= '&f.textAutocompleteAccents.facet.prefix='.urlencode(strtolower(trim(@implode('+', $tempQueryArray))));
            } else {
                $tempQueryArray[] = $word;
            }
        }
        return $queryUrl;
    }
    
    public static function searchAutocomplete($queryText)
    {
        $suggestions = array('keywords' => array(), 'rawkeywords' => array(), 'keywords_count' => array());
        if (!self::enableShowKeywordSuggestion()) {
            return $suggestions;
        }
        $queryText = strtolower($queryText);
        
        $connection = new \Solrbridge\Solr\Library\Client();
        $connection->setLiteMode();
        
        $solrServerUrl = self::getSolrServerUrl();
        $solrCore = self::getSolrCore();
        
        $connection->setSolrServerUrl($solrServerUrl);
        $queryUrl = $connection->buildUrl('select', $solrCore);
        $arguments = array(
            'q' => $queryText,
            'json.nl' => 'map',
            'spellcheck' => 'true',
            'spellcheck.collate' => 'true',
            'facet' => 'true',
            'facet.mincount' => 1,
            'facet.limit' => self::getKeywordSuggestionLimit(),
            //'facet.field' => 'textSearchStandard',
            'fq' => self::getFilterQuery(),
            'timestamp' => time(),
            'mm' => '0%',
            'rows' => 0,
            'defType'=> 'edismax',
            'wt'=> 'json',
        );
        
        $paramsString = http_build_query($arguments);
        //$queryUrl .= '?'.$paramsString;
        
        $facetFieldsString = '&facet.field=textAutocomplete&facet.field=textAutocompleteAccents';
        
        //Apply facet prefix for query url
        $facetPrefixUrl = self::_getFacetPrefixUrl($queryText);
        $autocompleteQueryUrl = $queryUrl.'?'.$paramsString.$facetFieldsString.$facetPrefixUrl;
        
        $result = $connection->doRequest($autocompleteQueryUrl);
        //If first query return empty suggestion, then check synonym
        if (!self::_resultHasSuggestion($result, 'textAutocomplete')) {
            $synonyms = $connection->getSynonyms($solrCore, $queryText);
            if (is_array($synonyms) && isset($synonyms[0])) {
                //New request to solr with synonym
                $facetPrefixUrl = self::_getFacetPrefixUrl($synonyms[0]);
                $autocompleteQueryUrl = $queryUrl.'?'.$paramsString.$facetFieldsString.$facetPrefixUrl;
                $result = $connection->doRequest($autocompleteQueryUrl);
            }
        }
        //If no synonym, then spell check query, didyoumean is true
        if (!self::_resultFoundOk($result)) {
            $suggestedSpellText = self::_getSuggestedSpellText($result);
            if (!empty($suggestedSpellText)) {
                $arguments['q'] = $suggestedSpellText;
                $paramsString = http_build_query($arguments);
                $facetFieldsString = '&facet.field=textAutocomplete&facet.field=textAutocompleteAccents';
                //Apply facet prefix for query url
                $facetPrefixUrl = self::_getFacetPrefixUrl($suggestedSpellText);
                $autocompleteQueryUrl = $queryUrl.'?'.$paramsString.$facetFieldsString;//.$facetPrefixUrl;
                $result = $connection->doRequest($autocompleteQueryUrl);
                self::$_queryText = $suggestedSpellText;
                self::$_didYouMean = $suggestedSpellText;
            }
        }
        
        $result['keywords'] = array();
        $result['rawkeywords'] = array();
        $result['keywords_count'] = array();
        
        self::_collectSuggestionTerms($result, 'textAutocompleteAccents');
        self::_collectSuggestionTerms($result, 'textAutocomplete');
        
        return array(
            'keywords' => $result['keywords'],
            'rawkeywords' => $result['rawkeywords'],
            'keywords_count' => $result['keywords_count']
        );
    }
    
    public static function _resultHasSuggestion($result, $autocompleteField)
    {
        if (isset($result['facet_counts']['facet_fields'][$autocompleteField]) &&
            is_array($result['facet_counts']['facet_fields'][$autocompleteField]) &&
            count($result['facet_counts']['facet_fields'][$autocompleteField]) > 0
        ) {
            return true;
        }
        return false;
    }
    
    public static function _getSuggestedSpellText($result)
    {
        $suggestedQueryText = null;
        if (isset($result['spellcheck']['collations']['collation'])) {
            $suggestedQueryText = strtolower($result['spellcheck']['collations']['collation']);
        }
        return $suggestedQueryText;
    }
    
    public static function _resultFoundOk($result)
    {
        if (isset($result['response']['numFound']) && intval($result['response']['numFound']) > 0) {
            return true;
        }
        return false;
    }
    
    public static function _collectSuggestionTerms(&$result, $autocompleteField)
    {
        if (isset($result['facet_counts']['facet_fields'][$autocompleteField]) && is_array($result['facet_counts']['facet_fields'][$autocompleteField])) {
            foreach ($result['facet_counts']['facet_fields'][$autocompleteField] as $term => $val) {
                self::_injectSuggestionTerm($result, $term, $val);
            }
        }
    }
    
    public static function _injectSuggestionTerm(&$result, $term, $count)
    {
        $suggestionTerm = trim($term, ',');
        if (!in_array($suggestionTerm, $result['rawkeywords'])) {
            $result['keywords'][] = self::hightlight(self::$_queryText, $suggestionTerm);
            $result['rawkeywords'][] = $suggestionTerm;
            $result['keywords_count'][] = $count;
        }
    }
    
    public static function loadConfigData($storeId = 0)
    {
        if (null === self::$_config) {
            $configFilePath = self::getConfigFilePath($storeId);
            $configData = file_get_contents($configFilePath);
            self::$_config = unserialize($configData);
        }
        return self::$_config;
    }
    
    public static function _getArrayValueByPath($indexPath, $arrayToAccess)
    {
        $indexPathArray = explode('/', $indexPath);
        $index = '[\''.str_replace('/', "']['", $indexPath).'\']';
        eval('$return=$arrayToAccess'.$index.';');
        return $return;
    }
    
    public static function getConfigData($path = null, $storeId = null)
    {
        if (!$storeId) {
            $storeId = self::$_storeId;
        }
        $config = self::loadConfigData($storeId);
        if (!$path) {
            return $config;
        }
        
        return self::_getArrayValueByPath($path, $config);
    }
    
    public static function formatPrice($price)
    {
        if ($price > 0) {
            $symbol = self::getConfigData('currency/symbol');
            return $symbol.number_format($price, 2, '.', ',');
        }
        return '';
    }
    
    /**
    * Solr server url should be like this http(s)://ipaddress[or domain]:[portnumber]/solr/
    */
    public static function getSolrServerUrl()
    {
        return self::getConfigData('solrbridge_general/solr/server_url');
    }
    
    public static function getSolrCore()
    {
        return self::getConfigData('solrbridge_general/solr/core');
    }
    
    public static function getProductSuggestionLimit()
    {
        //If advanced mode enabled
        if (self::isAdvancedMode()) {
            return 20;
        }
        
        $limit = self::getConfigData('solrbridge_autocomplete/autocomplete/product_suggestion_limit');
        if (is_numeric($limit)) {
            self::$_productSuggestionLimit = $limit;
        }
        return self::$_productSuggestionLimit;
    }
    
    public static function enableShowProductSuggestion()
    {
        return (boolean) self::getConfigData('solrbridge_autocomplete/autocomplete/show_product_suggestion');
    }
    
    public static function isAdvancedMode()
    {
        return (boolean) self::getConfigData('solrbridge_autocomplete/autocomplete/use_advanced_mode');
    }
    
    public static function getCategorySuggestionLimit()
    {
        //If advanced mode enabled
        if (self::isAdvancedMode()) {
            return 200;
        }
        
        $limit = self::getConfigData('solrbridge_autocomplete/autocomplete/category_suggestion_limit');
        if (is_numeric($limit)) {
            self::$_categorySuggestionLimit = $limit;
        }
        return self::$_categorySuggestionLimit;
    }
    
    public static function enableShowCategorySuggestion()
    {
        return (boolean) self::getConfigData('solrbridge_autocomplete/autocomplete/show_category_suggestion');
    }
    
    public static function getAutocompleteBrandAttributeCode()
    {
        $attributeCode = '';
        $enable = (boolean) self::getConfigData('solrbridge_autocomplete/autocomplete/show_brand_suggestion');
        $attrCode = self::getConfigData('solrbridge_autocomplete/autocomplete/brand_attribute_code');
        if ($enable && !empty($attrCode)) {
            $attributeCode = $attrCode;
        }
        return $attributeCode;
    }
    
    public static function getFacetFields()
    {
        $facetFields = array();
        $facetFields[] = 'cat_path';
        if ($autocompleteBrandAttrCode = self::getAutocompleteBrandAttributeCode()) {
            $facetFields[] = $autocompleteBrandAttrCode.'_autocomplete_facet';
        }
        //if multiple mode enabled
        $filterableAttrs = self::getConfigData('solrbridge_layer_nav/filterable_attributes');
        if (is_array($filterableAttrs) && count($filterableAttrs) > 0) {
            foreach (array_keys($filterableAttrs) as $attrCode) {
                $facetFields[] = $attrCode.'_facet';
            }
        }
        return $facetFields;
    }
    
    public static function getFilterQuery()
    {
        $fq = '(store_id:'.self::$_storeId.')';
        $filters = array();
        if (isset($_GET['fq'])) {
            $filters = json_decode($_GET['fq'], true);
        }
        if (is_array($filters) && count($filters) > 0) {
            foreach ($filters as $field => $value) {
                if (!is_array($value)) {
                    $fq .= ' AND (' . self::getFilterKey($field).':"' . urldecode($value) .'")';
                } else {
                    if ($field == 'price') {
                        $part = '';
                        foreach ($value as $val) {
                            $val = str_replace('-', ' TO ', $val);
                            $part .= '('.self::getFilterKey($field).':['.urldecode(trim($val).'.99999').']) OR ';
                        }
                        $part = trim(trim($part), 'OR');
                        if (!empty($part)) {
                            $fq .= ' AND ('.trim($part).')';
                        }
                    } else {
                        $part = '';
                        foreach ($value as $val) {
                            $part .= '('.self::getFilterKey($field).':"'.urldecode($val).'") OR ';
                        }
                        $part = trim(trim($part), 'OR');
                        if (!empty($part)) {
                            $fq .= ' AND ('.trim($part).')';
                        }
                    }
                }
            }
        }
        return $fq;
    }
    
    public static function getFilterKey($key)
    {
        if ($key == 'price') {
            $key .= '_decimal';
        } else {
            $key .= '_facet';
        }
        return $key;
    }
    
    public static function searchProducts($queryText)
    {
        $products = array(
            'products' => array(),
            'categories' => array(),
            'brands' => array(),
            'filters' => array(),
            'last_page_num' => 0
        );
        
        if (!self::enableShowProductSuggestion() && !self::enableShowCategorySuggestion()) {
            return $products;
        }
        
        $connection = new \Solrbridge\Solr\Library\Client();
        $connection->setLiteMode();
        
        $solrServerUrl = self::getSolrServerUrl();
        $solrCore = self::getSolrCore();
        
        $connection->setSolrServerUrl($solrServerUrl);
        $queryUrl = $connection->buildUrl('select', $solrCore);
        
        if (self::$_queryText) {
            $queryText = self::$_queryText;
        }
        
        $start = 0;
        if (isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p'] > 0) {
            $start = ($_GET['p'] - 1) * self::getProductSuggestionLimit();
        }
        
        $arguments = array(
            'q' => '"'.$queryText.'"',
            'qf' => 'textSearchStandard^80 textSearch^40 textSearchText^10 textSearchGeneral^1',
            'fl' => 'document_id,document_type,store_id,website_id,name_varchar,url_varchar,price_decimal,image_path_varchar',
            'json.nl' => 'map',
            'spellcheck' => 'true',
            'spellcheck.collate' => 'true',
            'facet' => 'true',
            'facet.mincount' => 1,
            'facet.limit' => self::getCategorySuggestionLimit(),
            //'facet.field' => 'cat_path',
            'fq' => self::getFilterQuery(),
            'timestamp' => time(),
            'mm' => '100%',
            'rows' => self::getProductSuggestionLimit(),
            'defType'=> 'edismax',
            'wt'=> 'json',
            'start' => $start
        );
        $paramsString = http_build_query($arguments);
        $queryUrl .= '?'.$paramsString;
        
        $facetFields = self::getFacetFields();
        if (is_array($facetFields) && count($facetFields) > 0) {
            $queryUrl .= '&facet.field='.@implode('&facet.field=', $facetFields);
        }
        /*
        $queryUrl .= '&facet.field=cat_path';
        $autocompleteBrandAttrCode = '';
        if ($autocompleteBrandAttrCode = self::getAutocompleteBrandAttributeCode()) {
            $queryUrl .= '&facet.field='.$autocompleteBrandAttrCode.'_autocomplete_facet';
        }*/
        
        $result = $connection->doRequest($queryUrl);
        if (!self::_resultFoundOk($result)) {
            $arguments['q'] = $queryText;
            $arguments['mm'] = '0%';
            
            $queryUrl = $connection->buildUrl('select', $solrCore);
            
            $paramsString = http_build_query($arguments);
            $queryUrl .= '?'.$paramsString;
        
            $facetFields = self::getFacetFields();
            if (is_array($facetFields) && count($facetFields) > 0) {
                $queryUrl .= '&facet.field='.@implode('&facet.field=', $facetFields);
            }
            $result = $connection->doRequest($queryUrl);
        }
        
        //Products
        if (self::enableShowProductSuggestion()) {
            if (isset($result['response']['numFound']) &&
                intval($result['response']['numFound']) > 0 &&
                is_array($result['response']['docs'])
            ) {
                foreach ($result['response']['docs'] as $k => $document) {
                    $priceDecimal = isset($document['price_decimal']) ? $document['price_decimal']: 0;
                    $productData = array(
                        'name' => self::hightlight(self::$_queryText, $document['name_varchar']),
                        'image' => $document['image_path_varchar'],
                        'productid' => $document['document_id'],
                        'producturl' => $document['url_varchar'],
                        'price' => $priceDecimal,
                        'currency' => 'USD',
                        'formatted_price' => self::formatPrice($priceDecimal)
                    );
                    $products['products'][] = $productData;
                }
                
                //Set last page number
                $lastPageNumber = (intval($result['response']['numFound']) / self::getProductSuggestionLimit());
                $lastPageNumber = ceil($lastPageNumber);
                $products['last_page_num'] = $lastPageNumber;
            }
        }
        //Categories
        if (self::enableShowCategorySuggestion()) {
            $categoryFacetData = self::_getFacetData('cat_path', $result);
            //If not Advanced enabled
            if (!self::isAdvancedMode()) {
                if (is_array($categoryFacetData) && count($categoryFacetData) > 0) {
                    $formattedCategoryFacetData = array();
                    foreach ($categoryFacetData as $term => $count) {
                        //$formattedTerm = self::hightlight(self::$_queryText, $term);
                        $formattedCategoryFacetData[] = self::_parseCategoryFromString($term, $count);
                    }
                    $products['categories'] = $formattedCategoryFacetData;
                }
            } else {
                require_once MAGENTO2_ROOT.FS.'app'.FS.'code'.FS.'Solrbridge'.FS.'Search'.FS.'Helper'.FS.'Category.php';
                //If advanced enable
                $categoryHelper = new \Solrbridge\Search\Helper\Category();
                $categoryHelper->setCategoryPathData($categoryFacetData);
                $products['categories'] = $categoryHelper->toHtml();
            }
        }
        
        //Brands
        $autocompleteBrandAttrCode = self::getAutocompleteBrandAttributeCode();
        if ($autocompleteBrandAttrCode) {
            $brandFacetData = self::_getFacetData($autocompleteBrandAttrCode.'_autocomplete_facet', $result);
            if (is_array($brandFacetData) && count($brandFacetData) > 0) {
                $formattedBrandFacetData = array();
                foreach ($brandFacetData as $term => $count) {
                    //$formattedTerm = self::hightlight(self::$_queryText, $term);
                    $formattedBrandFacetData[] = self::_parseBrandFromString($term, $count);
                }
                $products['brands'] = $formattedBrandFacetData;
            }
        }
        
        //If Advanced mode enabled
        if (self::isAdvancedMode()) {
            $filterableAttrs = self::getConfigData('solrbridge_layer_nav/filterable_attributes');
            $facetFields = self::getFacetFields();
            if (is_array($facetFields) && count($facetFields) > 0) {
                foreach ($facetFields as $facetKey) {
                    $facetItems = self::_getFacetData($facetKey, $result);
                    if (is_array($facetItems) && count($facetItems) > 0) {
                        $attrCode = self::getAttributeCodeFromFacet($facetKey);
                        if (isset($filterableAttrs[$attrCode])) {
                            $facetData = $filterableAttrs[$attrCode];
                            $facetData['attribute_code'] = $attrCode;
                            foreach ($facetItems as $facetValue => $count) {
                                if (isset($facetData['options'][$facetValue])) {
                                    $facetData['items'][] = array(
                                        'label' => $facetData['options'][$facetValue],
                                        'value' => $facetValue,
                                        'attribute_code' => $attrCode,
                                        'count' => $count
                                    );
                                }
                            }
                            //$facetData['items'] = $facetItems;
                            $products['filters'][$attrCode] = $facetData;
                        }
                    }
                }
            }
        }
        return $products;
    }
    
    public static function getAttributeCodeFromFacet($facetCode)
    {
        $dataArray = explode('_', $facetCode);
        $count = count($dataArray);
        if ($count > 1) {
            unset($dataArray[($count - 1)]);
        }
        return @implode('_', $dataArray);
    }
    
    public static function _parseBrandFromString($brandString, $count)
    {
        $brandId = substr($brandString, (strrpos($brandString, '/') + 1), strlen($brandString));
        $brandName = substr($brandString, 0, strrpos($brandString, '/'));
        return array('name' => $brandName, 'id' => $brandId, 'count' => $count);
    }
    
    public static function _parseCategoryFromString($categoryString, $count)
    {
        $categoryData = self::pathToArray($categoryString);
        foreach ($categoryData as $k => $value) {
            $categoryData[$k]['name'] = self::hightlight(self::$_queryText, $categoryData[$k]['name']);
            $categoryData[$k]['count'] = $count;
        }
        return $categoryData;
    }
    
    /**
     * Convert string path to array
     * @param string $path
     * @return array
     */
    public static function pathToArray($path)
    {
        $chunks = explode('/', $path);
        $result = array();
        for ($i = 0; $i < sizeof($chunks) - 1; $i+=2) {
            //$result[] = array('id' => $chunks[($i+1)], 'name' => $chunks[$i]);
            //CatId format x:y, x is category id, y is position
            $catIdArr = explode(':', $chunks[($i+1)]);
            $result[] = array('id' => $catIdArr[0], 'position' => $catIdArr[1], 'name' => $chunks[$i]);
        }

        return $result;
    }
    
    public static function _getFacetData($facetField, $response)
    {
        $data = array();
        if (isset($response['facet_counts']['facet_fields'][$facetField])) {
            $data = $response['facet_counts']['facet_fields'][$facetField];
        }
        
        if (!is_array($data)) {
            $data = (array)$data;
        }
        return $data;
    }
    
    public static function search($queryText, $storeId)
    {
        self::$_storeId = $storeId;
        self::$_queryText = $queryText;
        $results['keywords'] = self::searchAutocomplete($queryText);
        $productData = self::searchProducts($queryText);
        $results['products'] = $productData['products'];
        $results['categories'] = $productData['categories'];
        $results['brands'] = $productData['brands'];
        $results['filters'] = $productData['filters'];
        $results['last_page_num'] = $productData['last_page_num'];
        $results['q'] = self::$_queryText;
        $results['didyoumean'] = self::$_didYouMean;
        //$results['urls'] = self::getConfigData('urls');
        return $results;
    }
    
    public static function query($queryText, $storeId, $timeStamp)
    {
        $searchResult = self::search($queryText, $storeId);

        $data = array(
            'status' => 'ERROR',
            'q' => $searchResult['q'],
            'timestamp' => $timeStamp,
            //'keywords' => array('hello', 'word', 'bak', 'foo', 'bar'),
            'keywords' => array(),
            'rawkeywords' => array(),
            'keywords_count' => array(),
            /*
            'products' => array(
                array(
                    'name' => 'Product 1',
                    'image' => '',
                    'productid' => 2,
                    'price' => 99.99,
                    'currency' => 'USD',
                    'formatted_price' => '$99.99'
                ),
                array(
                    'name' => 'Product 2',
                    'image' => '',
                    'productid' => 1,
                    'price' => 99.99,
                    'currency' => 'USD',
                    'formatted_price' => '$99.99'
                ),
            )*/
            'products' => $searchResult['products'],
            'categories' => $searchResult['categories'],
            'brands' => $searchResult['brands'],
            'filters' => $searchResult['filters'],
            'last_page_num' => $searchResult['last_page_num'],
            'didyoumean' => $searchResult['didyoumean']
        );
        $autocompleteResult = $searchResult['keywords'];
        $data = array_merge($data, $autocompleteResult);

        //validation
        if( isset($data['keywords']) && is_array($data['keywords']) && count($data['keywords']) > 0)
        {
            $data['status'] = 'OK';
        }
        if( $data['status'] == 'ERROR' )
        {
            if( isset($data['products']) && is_array($data['products']) && count($data['products']) > 0)
            {
                $data['status'] = 'OK';
            }
        }
    
        return $data;
    }
}
