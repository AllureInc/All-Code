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
namespace Solrbridge\Search\Block;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    /**
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Solrbridge\Search\Model\Layer\Resolver $layerResolver,
        \Solrbridge\Search\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
    }
    
    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->renderer = $this->getChildBlock('renderer');
        foreach ($this->filterList->getFilters($this->_catalogLayer) as $filter) {
            $filter->apply($this->getRequest());
        }
        //$this->getLayer()->apply();
        //return parent::_prepareLayout();
    }
    
    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        //Magento\Catalog\Model\Layer\FilterList
        return $this->filterList->getFilters($this->_catalogLayer);
    }
}
