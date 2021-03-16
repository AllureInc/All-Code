<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade SolrBridge to newer
 * versions in the future.
 *
 * @category    SolrBridge
 * @package     SolrBridge_Search
 * @author      Hau Danh
 * @copyright   Copyright (c) 2011-2017 SolrBridge (http://www.solrbridge.com)
 */
namespace Solrbridge\Search\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;
use Solrbridge\Search\Helper\Utility;
use Solrbridge\Search\Helper\System;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    protected $isAllowMultiple = false;
    
    protected $filterQuery = null;
    
    protected $filterKey = null;
    
    protected $renderAttrAsDropdown = null;
    
    public function getRenderAttrAsDropdown()
    {
        if (null === $this->renderAttrAsDropdown) {
            $this->renderAttrAsDropdown = System::getHelper()
                                            ->getLayerNavSetting('general/render_all_attr_as_dropdown');
        }
        return $this->renderAttrAsDropdown;
    }
    
    protected function getFilterQuery()
    {
        if (null === $this->filterQuery) {
            $this->filterQuery = array();
            $filterQuery = $this->getRequest()->getParam('fq');
            if (is_array($filterQuery) && count($filterQuery) > 0) {
                $this->filterQuery = $filterQuery;
            }
        }
        return $this->filterQuery;
    }
    
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->isAllowMultiple = false;
        if ($filter->getData('is_allow_multiple')) {
            $this->isAllowMultiple = $filter->getData('is_allow_multiple');
        }
        
        $this->filterKey = $filter->getRequestVar();
        
        if ($filter->getData('filter_type') == DoctypeHandler::CATEGORY_PATH_KEY) {
            //implement logic here
            $this->assign('filterItems', []);
            return $filter->getData('html_data');
        } else {
            if ($this->getRenderAttrAsDropdown()) {
                $renderAsDropdown = $this->getRenderAttrAsDropdown();
            } else {
                $renderAsDropdown = $filter->getData('render_as_dropdown');
            }
            
            $this->assign('render_as_dropdown', $renderAsDropdown);
            $this->assign('remove_url', $filter->getRemoveUrl());
        }
        return parent::render($filter);
    }
    
    public function getMultipleFilterClass()
    {
        if ($this->isAllowMultiple) {
            return ' sb-allow-multiple-filter';
        }
        return '';
    }
    
    public function getFilterItemActiveClass($filterItem)
    {
        //$filterKey = $filter->getRequestVar();
        $filterQuery = $this->getFilterQuery();
        if (isset($filterQuery[$this->filterKey])) {
            if (is_array($filterQuery[$this->filterKey])) {
                if (in_array($filterItem->getValue(), $filterQuery[$this->filterKey])) {
                    return ' active';
                }
            }
        }
        return '';
        //return ' '.$this->filterKey.':'.$filterItem->getValue().':'.print_r($filterQuery, true);
    }
    
    /**
     * Render block HTML
     * This function is backed up for removing later
     * @return string
     */
    protected function toHtmlBackUp()
    {
        $html = parent::_toHtml();
        //Add html class if this filter is allow multiple selection
        if ($this->isAllowMultiple && !empty($html)) {
            $xPosition = strpos($html, '>');
            $remainHtml = substr($html, $xPosition, strlen($html));
            $openTag = substr($html, 0, $xPosition);
            $openTag = trim($openTag);

            if (strlen($openTag) > 0) {
                if (preg_match('/class=\"(.*?)\"/i', $openTag, $matches)) {
                    $openTag = preg_replace('/class=\"(.*?)\"/i', 'class="$1 sb-allow-multiple-filter"', $openTag);
                } else {
                    $openTag .= ' class="sb-allow-multiple-filter"';
                }
                $html = $openTag . $remainHtml;
            }
        }
        return $html;
    }
}
