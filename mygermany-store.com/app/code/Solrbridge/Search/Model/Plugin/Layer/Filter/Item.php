<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Model\Plugin\Layer\Filter;

/**
 * Class FilterRenderer
 */
class Item
{
    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Html pager block
     *
     * @var \Magento\Theme\Block\Html\Pager
     */
    protected $htmlPagerBlock;
    
    protected $request;
    
    protected $utilityHelper;
    
    protected $registry;

    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\App\RequestInterface $request,
        \Solrbridge\Search\Helper\Utility $utilityHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->url = $url;
        $this->utilityHelper = $utilityHelper;
        $this->request = $request;
        $this->htmlPagerBlock = $htmlPagerBlock;
        $this->registry = $registry;
    }
    
    public function aroundGetUrl(
        \Magento\Catalog\Model\Layer\Filter\Item $subject,
        \Closure $proceed
    ) {
        
        $searchResult = $this->registry->registry('solrbridge_search_result');
        if (!$searchResult) {
            return $proceed();
        }
        
        $filterQuery = $this->request->getParam('fq');
        
        $filterKey = $subject->getFilter()->getRequestVar();
        
        $params = array();
        
        //If $filterKey exists in $filterQuery
        if (isset($filterQuery[$filterKey])) {
            if (is_array($filterQuery[$filterKey]) && !in_array($subject->getValue(), $filterQuery[$filterKey])) {
                $params = array(
                    $subject->getFilter()->getRequestVar() => $this->utilityHelper->mergeFilterQueryRecusive($filterQuery[$filterKey], array($subject->getValue())),
                );
            }
        } else {
            $params = array(
                $subject->getFilter()->getRequestVar() => array($subject->getValue()),
            );
        }
        
        $query = [
            'fq' => $this->utilityHelper->mergeFilterQueryRecusive($filterQuery, $params),
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        
        return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
    
    public function afterGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $result)
    {
        $searchResult = $this->registry->registry('solrbridge_search_result');
        if (!$searchResult) {
            return $result;
        }
        $filterQuery = $this->request->getParam('fq');
        
        $filterKey = $item->getFilter()->getRequestVar();
        
        $params = array();
        
        //If $filterKey exists in $filterQuery
        if (isset($filterQuery[$filterKey])) {
            $index = array_search($item->getValue(), $filterQuery[$filterKey]);
            if (false !== $index) {
                unset($filterQuery[$filterKey][$index]);
                if (count($filterQuery[$filterKey]) < 1) {
                    unset($filterQuery[$filterKey]);
                }
            }
        }
        
        $query = [
            'fq' => $filterQuery,
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        $result = $this->url->getUrl('*/*/*', $params);
        return $result;
    }
}
