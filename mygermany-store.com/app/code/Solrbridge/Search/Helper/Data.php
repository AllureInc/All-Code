<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\Helper;

use Magento\Search\Model\QueryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\Query as SearchQuery;
use Solrbridge\Search\Helper\System;
use Solrbridge\Search\Helper\Utility;

/**
 * Contact base helper
 */
class Data extends \Magento\Search\Helper\Data
{
    const CONFIG_AUTOCOMPLETE_BRAND_ATTR_CODE = 'solrbridge_autocomplete/autocomplete/brand_attribute_code';
    private $assetRepo;
    
    protected $_didYouMeanQueryText = null;
    
    protected $catalogMediaConfig;
    
    protected $categoryManagement;
    
    protected $categories = null;
    /**
     * Construct
     *
     * @param Context $context
     * @param StringUtils $string
     * @param Escaper $escaper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Escaper $escaper,
        StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Catalog\Model\Product\Media\Config $catalogMediaConfig,
        \Magento\Catalog\Api\CategoryManagementInterface $categoryManagement
    ) {
        $this->assetRepo = $assetRepo;
        $this->catalogMediaConfig = $catalogMediaConfig;
        $this->categoryManagement = $categoryManagement;
        parent::__construct($context, $string, $escaper, $storeManager);
    }
    
    public function getSolrResultCacheKey()
    {
        $params = $this->_request->getParams();
        $params['store_id'] = $this->storeManager->getStore()->getId();
        if (isset($params['_'])) {
            unset($params['_']);
        }
        return hash('md5', json_encode($params));
    }
    
    public function isCategoryViewPage()
    {
        return (boolean) System::getRegistry()->registry('solrbridge_search_catalog_category_view');
    }
    
    public function setDidYouMeanQueryText($queryText)
    {
        $this->_didYouMeanQueryText = $queryText;
        return $this;
    }
    
    /**
     * Retrieve HTML escaped search query
     *
     * @return string
     */
    public function getEscapedQueryText()
    {
        $queryText = $this->getQueryText();
        if (null !== $this->_didYouMeanQueryText) {
            $queryText = $this->_didYouMeanQueryText;
        }
        return $this->escaper->escapeHtml(
            $this->getPreparedQueryText($queryText, $this->getMaxQueryLength())
        );
    }
    
    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return bool
     */
    private function isQueryTooLong($queryText, $maxQueryLength)
    {
        return ($maxQueryLength !== '' && $this->string->strlen($queryText) > $maxQueryLength);
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getQueryText()
    {
        $queryText = $this->_request->getParam($this->getQueryParamName());
        return($queryText === null || is_array($queryText))
            ? ''
            : $this->string->cleanString(trim($queryText));
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return string
     */
    private function getPreparedQueryText($queryText, $maxQueryLength)
    {
        if ($this->isQueryTooLong($queryText, $maxQueryLength)) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        return $queryText;
    }
    
    public function getResultUrl($query = null)
    {
        if (is_array($query)) {
            $queryData = $query;
        } else {
            $queryData = [QueryFactory::QUERY_VAR_NAME => $query];
        }
        return $this->_getUrl(
            'search/result/index',
            ['_query' => $queryData, '_secure' => $this->_request->isSecure()]
        );
    }
    
    public function getResultRedirectUrl()
    {
        return $this->_getUrl(
            'sbsearch/result/redirect',
            ['_query' => [], '_secure' => $this->_request->isSecure()]
        );
    }
    
    public function getLoadProductUrl()
    {
        return $this->_getUrl(
            'sbsearch/result/loadproduct',
            ['_query' => [], '_secure' => $this->_request->isSecure()]
        );
    }
    
    public function getLoadProductDataUrl()
    {
        return $this->_getUrl('sbsearch/result/loadproductdata', ['_secure' => $this->_request->isSecure()]);
    }
    
    public function getSpinner()
    {
        $params = array('_secure' => $this->_request->isSecure());
        return $this->assetRepo->getUrlWithParams('Solrbridge_Search::images/spinner.gif', $params);
    }
    
    public function getAutocompleteBrandAttributeCode($store = null)
    {
        if (!$store) {
            $store = $this->storeManager->getStore();
        }
        return $store->getConfig(self::CONFIG_AUTOCOMPLETE_BRAND_ATTR_CODE);
    }
    
    public function getLayerNavSetting($path = null, $store = null)
    {
        $value = null;
        if (!$store) {
            $store = $this->storeManager->getStore();
        }
        
        if (!$path) {
            return null;
        }
        
        $path = 'solrbridge_layernav/'.trim($path, '/');
        
        return $store->getConfig($path);
    }
    
    public function getAutocompleteSetting($path = null, $store = null)
    {
        $value = null;
        if (!$store) {
            $store = $this->storeManager->getStore();
        }
        
        if (!$path) {
            return null;
        }
        
        $path = 'solrbridge_autocomplete/'.trim($path, '/');
        
        return $store->getConfig($path);
    }
    
    public function isAdvancedMode()
    {
        return $this->getAutocompleteSetting('autocomplete/use_advanced_mode');
    }
    
    public function getGeneralSetting($path = null, $store = null)
    {
        $value = null;
        if (!$store) {
            $store = $this->storeManager->getStore();
        }
        
        if (!$path) {
            return null;
        }
        
        $path = 'solrbridge_general/'.trim($path, '/');
        
        return $store->getConfig($path);
    }
    
    public function getAutocompleteThumbSize()
    {
        $thumbSize = $this->getAutocompleteSetting('autocomplete/thumb_size');
        $data = @explode('x', $thumbSize);
        $w = 50;
        $h = 50;
        if (isset($data[0]) && isset($data[1]) && $data[0] > 0 && $data[1] > 0) {
            $w = $data[0];
            $h = $data[1];
        }
        return array('w' => $w, 'h' => $h);
    }
    
    public function getCategories()
    {
        if (null === $this->categories) {
            //get first level categories
            $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
            $this->categories = $this->categoryManagement->getTree($rootCategoryId, 1);
        }
        return $this->categories;
    }
    
    public function isCategoryEnabledInSearchBox()
    {
        return $this->getAutocompleteSetting('autocomplete/display_category_dropdown');
    }
    
    public function getSelectedCategoryItem($categoryId)
    {
        $filterQuery = Utility::getFilterQuery();
        if(isset($filterQuery['cat'])) {
            if(is_array($filterQuery['cat'])) {
                if(in_array($categoryId, $filterQuery['cat'])) {
                    return 'selected="selected"';
                }
            } else {
                if($categoryId == $filterQuery['cat']) {
                    return 'selected="selected"';
                }
            }
        }
        return '';
    }
    
    public function getAutocompleteJsonConfig()
    {
        $thumbSize = $this->getAutocompleteThumbSize();
        $config = array(
            'store_id' => $this->storeManager->getStore()->getId(),
            'load_product_url' => $this->getLoadProductUrl(),
            'load_product_data_url' => $this->getLoadProductDataUrl(),
            'result_redirect_url' => $this->getResultRedirectUrl(),
            'result_url' => $this->getResultUrl(),
            'base_url' => $this->_urlBuilder->getBaseUrl(),
            'base_media_url' => $this->catalogMediaConfig->getBaseMediaUrl(),
            'brand_attr_code' => $this->getAutocompleteBrandAttributeCode(),
            'spinner' => $this->getSpinner(),
            'advanced_mode' => $this->isAdvancedMode(),
            'thumbWidth' => $thumbSize['w'],
            'thumbHeight' => $thumbSize['h']
        );
        if ($this->isCategoryEnabledInSearchBox()) {
            $config['show_cat_dropdown'] = 1;
            $filterQuery = Utility::getFilterQuery();
            if(isset($filterQuery['cat'])) {
                $config['selected_cat_id'] = $filterQuery['cat'];
            }
        }
        
        return json_encode($config);
    }
    
    public function getTopLevelCategories()
    {
        $categories = $this->getCategories();
        $defaultCategoryId = $categories->getId();
        $categories = $categories->getChildrenData();
        
        //$selected = $this->getSelectedCategoryItem($defaultCategoryId);
        $returnCategories = [];
        $returnCategories[] = [
            'id' => $defaultCategoryId,
            'name' => (string)__('All'),
            //'selected' => (trim($selected)) ? 1 : 0
        ];
        
        foreach ($categories as $category) {
            $selected = $this->getSelectedCategoryItem($category->getId());
            if ($category->getName()) {
                $returnCategories[] = [
                    'id' => $category->getId(),
                    'name' => (string) $category->getName(),
                    //'selected' => (trim($selected)) ? 1 : 0
                ];
            }
        }
        
        return $returnCategories;
    }
}
