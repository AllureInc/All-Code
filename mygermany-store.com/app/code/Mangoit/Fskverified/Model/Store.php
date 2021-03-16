<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Fskverified\Model;

use Magento\Catalog\Model\Category;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Url\ScopeInterface as UrlScopeInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Store model
 *
 * @api
 * @method Store setGroupId($value)
 * @method int getSortOrder()
 * @method int getStoreId()
 * @method Store setSortOrder($value)
 * @method Store setIsActive($value)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Store extends \Magento\Store\Model\Store
{
    public function getCustomerUrl()
    {
    }

    public function getCurrentUrl($fromStore = true)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeID       = $storeManager->getStore()->getStoreId(); 
        $sidQueryParam = $this->_sidResolver->getSessionIdQueryParam($this->_getSession());
        $requestString = $this->_url->escape(ltrim($this->_request->getRequestString(), '/'));

        $storeUrl = $this->getUrl('', ['_secure' => $storeManager->getStore()->isCurrentlySecure()]);

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        $storeParsedUrl = parse_url($storeUrl);

        $storeParsedQuery = [];
        if (isset($storeParsedUrl['query'])) {
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = $this->_request->getQueryValue();
        if (isset($currQuery[$sidQueryParam])
            && !empty($currQuery[$sidQueryParam])
            && $this->_getSession()->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]
        ) {
            unset($currQuery[$sidQueryParam]);
        }

        foreach ($currQuery as $key => $value) {
            $storeParsedQuery[$key] = $value;
        }

        if (!$this->isUseStoreInUrl()) {
            $storeParsedQuery['___store'] = $this->getCode();
        }
        if ($fromStore !== false) {
            $storeParsedQuery['___from_store'] = $fromStore ===
                true ? $storeManager->getStore()->getCode() : $fromStore;
        }

        $currentUrl = $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path']
            . $requestString;

        if( preg_match("/\?/",$currentUrl) ){
            $currentUrl .= ($storeParsedQuery ? '&amp;'.http_build_query($storeParsedQuery, '', '&amp;') : '');
        } else {
            $currentUrl .= ($storeParsedQuery ? '?' . http_build_query($storeParsedQuery, '', '&amp;') : '');
        }
        return $this->cleanUrlQueryString($currentUrl);

    }

    public function cleanUrlQueryString($url){
        if(preg_match("/\?/", $url)){
            $arr = explode('?', $url);
            $queryArray = false;
            if(isset($arr[1])){
                $strArr = parse_str(html_entity_decode($arr[1]), $queryArray);
            }
            $cleanedUrl = $arr[0];
            $cleanedUrl .= ($queryArray) ? '?'.http_build_query($queryArray, '', '&amp;') : '';
            return $cleanedUrl;
        }
        return $url;    
    }

}
