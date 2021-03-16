<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\Model\Layer;

use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;
use Solrbridge\Search\Helper\Utility;

class FilterList extends \Magento\Catalog\Model\Layer\FilterList
{
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param FilterableAttributeListInterface $filterableAttributes
     * @param array $filters
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Layer\FilterableAttributeListInterface $filterableAttributes,
        array $filters = []
    ) {
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;

        /** Override default filter type models */
        $this->filterTypes = array_merge($this->filterTypes, $filters);
    }
    
    protected function getAttributeByCode($code)
    {
        $attributeCode = $this->getAttributeCodeFromFacet($code);
        $returnAttribute = null;
        foreach ($this->filterableAttributes->getList() as $attribute) {
            if ($attributeCode == $attribute->getAttributeCode()) {
                $returnAttribute = $attribute;
                break;
            }
        }
        return $returnAttribute;
    }
    
    protected function isAllowToDisplay($filter, $isMultiple)
    {
        $facetKey = $filter->getRequestVar();
        $filterQuery = Utility::getFilterQuery();
        if (isset($filterQuery[$facetKey]) && !$isMultiple) {
            return false;
        }
        return true;
    }
    
    protected function getAttributeCodeFromFacet($facetCode)
    {
        $dataArray = explode('_', $facetCode);
        $count = count($dataArray);
        if ($count > 1) {
            unset($dataArray[($count - 1)]);
        }
        return @implode('_', $dataArray);
    }
    
    /**
     * Retrieve list of filters
     *
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array|Filter\AbstractFilter[]
     */
    public function getFilters(\Magento\Catalog\Model\Layer $layer)
    {
        $facetFields = array();
        $registry = $this->objectManager->get('Magento\Framework\Registry');
        
        if (!count($this->filters)) {
            $searchResult = $registry->registry('solrbridge_search_result');
            if ($searchResult) {
                //Collect facet fields from Solr search result
                if (isset($searchResult['facet_counts']['facet_fields'])) {
                    if (isset($searchResult['facet_counts']['facet_fields'][DoctypeHandler::CATEGORY_PATH_KEY])) {
                        $categoryFacetData = $searchResult['facet_counts']['facet_fields'][DoctypeHandler::CATEGORY_PATH_KEY];
                        $categoryArguments = array(
                            'layer' => $layer, 'data' => array(DoctypeHandler::CATEGORY_PATH_KEY => $categoryFacetData)
                        );
                        $this->filters = [
                            $this->objectManager->create($this->filterTypes[self::CATEGORY_FILTER], $categoryArguments)
                        ];
                        unset($searchResult['facet_counts']['facet_fields'][DoctypeHandler::CATEGORY_PATH_KEY]);
                    }
                    
                    foreach ($searchResult['facet_counts']['facet_fields'] as $facetCode => $facetData) {
                        if ($facetCode == 'price_facet') {
                            continue;
                        }
                        $attribute = $this->getAttributeByCode($facetCode);
                        if ($attribute) {
                            $attribute->setData('facet_data', $facetData);
                            $attribute->setData('solr_data', $searchResult);
                            $filter = $this->createAttributeFilter($attribute, $layer);
                            $isMultiple = Utility::isMultipleFilterAttr($searchResult, $attribute->getAttributeCode());
                            $filter->setData('is_allow_multiple', $isMultiple);
                            $filter->setData('render_as_dropdown', $attribute->getSolrbridgeSearchRenderAsDropdown());
                            $filter->setData('render_as_dropdown', $attribute->getSolrbridgeSearchRenderAsDropdown());
                            
                            if (!$this->isAllowToDisplay($filter, $isMultiple)) {
                                $filter->setItems([]);//disable this filter to show
                            }
                            
                            $this->filters[] = $filter;
                        }
                    }
                }
            } else {
                foreach ($this->filterableAttributes->getList() as $attribute) {
                    $facetFields[] = $attribute->getAttributeCode().'_facet';
                }
            }
        }
        if (null !== $registry->registry('search_facet_fields')) {
            $registry->unregister('search_facet_fields');
        }
        $registry->register('search_facet_fields', $facetFields);
        
        return $this->filters;
    }
    
    /**
     * Create filter
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\Layer $layer
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function createAttributeFilter(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $filterClassName = $this->getAttributeFilterClass($attribute);
        $filter = $this->objectManager->create(
            $filterClassName,
            ['data' => ['attribute_model' => $attribute], 'layer' => $layer]
        );
        return $filter;
    }

    /**
     * Get Attribute Filter Class Name
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return string
     */
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $filterClassName = $this->filterTypes[self::ATTRIBUTE_FILTER];

        if ($attribute->getAttributeCode() == 'price') {
            $filterClassName = $this->filterTypes[self::PRICE_FILTER];
        } elseif ($attribute->getBackendType() == 'decimal') {
            $filterClassName = $this->filterTypes[self::DECIMAL_FILTER];
        }

        return $filterClassName;
    }
}
