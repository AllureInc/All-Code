<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

use Solrbridge\Search\Helper\System;

/**
 * Contact base helper
 */
class Utility
{
    public static function getFilterQuery()
    {
        $filterQuery = array();
        $_filterQuery = System::getRequest()->getParam('fq');
        if (is_array($_filterQuery) && count($_filterQuery) > 0) {
            $filterQuery = $_filterQuery;
        }
        return $filterQuery;
    }
    
    public static function prepareFacetData(&$result, $store, $identifier)
    {
        $currentStoreId = $store->getId();
        $currentWebsiteId = $store->getWebsiteId();
        $key = sha1('solrbridge_search_'.$currentStoreId.'_'.$currentWebsiteId.'_'.$identifier);
        
        $facetData = array();
        if (isset($result['facet_counts']['facet_fields']) && is_array($result['facet_counts']['facet_fields'])) {
            $facetData = $result['facet_counts']['facet_fields'];
        }
        
        $cachedFacetData = System::getCatalogSession()->getSolrBridgeFacetData();
        if (isset($cachedFacetData) && isset($cachedFacetData[$key])) {
            $filterQuery = self::getFilterQuery();
            $filterQueryKeys = array_keys($filterQuery);
            foreach ($filterQueryKeys as $_attrCode) {
                $facetkey = $_attrCode.'_facet';
                if (isset($cachedFacetData[$key]['facet_counts']['facet_fields'][$facetkey]) &&
                    !empty($cachedFacetData[$key]['facet_counts']['facet_fields'][$facetkey]) &&
                    self::isMultipleFilterAttr($result, $_attrCode)
                ) {
                    $facetData[$facetkey] = $cachedFacetData[$key]['facet_counts']['facet_fields'][$facetkey];
                }
            }
        }
        
        $cachedFacetData[$key]['facet_counts']['facet_fields'] = $facetData;
        System::getCatalogSession()->setSolrBridgeFacetData($cachedFacetData);
        $result['facet_counts']['facet_fields'] = $facetData;
    }
    
    public static function getUniqueArrayData($dataArray = array())
    {
        $returnValues = array();
        if (is_array($dataArray)) {
            foreach ($dataArray as $_value) {
                $value = strtolower($_value);
                if (!in_array($value, $returnValues)) {
                    $returnValues[] = $value;
                }
            }
        }
        return $returnValues;
    }
    
    public static function getSearchWeightOptions()
    {
        $weights = array();
        $index = 1;
        foreach (range(10, 200, 10) as $number) {
            $weights[$index] = array(
                    'value' => $number,
                    'label' => $index
            );
            $index++;
        }
        return array_merge(array('0' => __('Default')), $weights);
    }
    
    public static function isMultipleFilterAttr($result, $attrCode)
    {
        if (isset($result['allowMultipleFilterAttributes']) &&
            is_array($result['allowMultipleFilterAttributes']) &&
            in_array($attrCode, $result['allowMultipleFilterAttributes'])
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * Array unique recusive
     * @param array $array
     * @return array:
     */
    public static function arrayUniqueRecusive($array)
    {
        $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::arrayUniqueRecusive($value);
            }
        }
        return $result;
    }

    public function cleanArray($array)
    {
        $array = array_flip($array);
        return array_flip(array_keys($array));
    }

    /**
     * Merge Array unique recusive
     * @param array $array1
     * @param array $array1
     * @return array:
     */
    public function mergeFilterQueryRecusive($array1, $array2)
    {
        if (!is_array($array1)) {
            $array1 = array();
        }
        if (!is_array($array2)) {
            $array2 = array();
        }
        
        //\Solrbridge\Search\Helper\Debug::log($array1, true);
        //\Solrbridge\Search\Helper\Debug::log($array2, true);
        
        $params = array_merge_recursive($array1, $array2);
        return $this->arrayUniqueRecusive($params);
    }
    
    public static function getSolrBridgeCachePath()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryList = $objectManager->get('Magento\Framework\Filesystem\DirectoryList');
        $ioAdapter = $objectManager->get('Magento\Framework\Filesystem\Io\File');
        $path = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::CACHE).DIRECTORY_SEPARATOR.'solrbridge';
        $ioAdapter->checkAndCreateFolder($path);
        return $path;
    }
    
    public static function clearSolrBridgeStaticConfigFiles()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ioAdapter = $objectManager->get('Magento\Framework\Filesystem\Io\File');
        $path = self::getSolrBridgeCachePath();
        $ioAdapter->rmdir($path, true);
    }
}
