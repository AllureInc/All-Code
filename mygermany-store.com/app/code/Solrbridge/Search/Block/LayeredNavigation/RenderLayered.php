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
namespace Solrbridge\Search\Block\LayeredNavigation;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\Entity\Attribute\Option;
use Solrbridge\Search\Model\Layer\Filter\Item as FilterItem;

class RenderLayered extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    protected $utilityHelper;
    
    /**
     * Html pager block
     *
     * @var \Magento\Theme\Block\Html\Pager
     */
    protected $htmlPagerBlock;
    
    /**
     * @param Template\Context $context
     * @param Attribute $eavAttribute
     * @param AttributeFactory $layerAttribute
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Helper\Media $mediaHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \Solrbridge\Search\Helper\Utility $utilityHelper,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        array $data = []
    ) {
        $this->utilityHelper = $utilityHelper;
        $this->htmlPagerBlock = $htmlPagerBlock;
        parent::__construct($context, $eavAttribute, $layerAttribute, $swatchHelper, $mediaHelper, $data);
    }
    
    /**
     * @return array
     */
    public function getSwatchData()
    {
        if (false === $this->eavAttribute instanceof Attribute) {
            throw new \RuntimeException('Magento_Swatches: RenderLayered: Attribute has not been set.');
        }
        
        $attributeOptions = [];
        foreach ($this->eavAttribute->getOptions() as $option) {
            if ($currentOption = $this->getFilterOption($this->filter->getItems(), $option)) {
                $attributeOptions[$option->getValue()] = $currentOption;
            } elseif ($this->isShowEmptyResults()) {
                $attributeOptions[$option->getValue()] = $this->getUnusedOption($option);
            }
        }
        
        /*
        $attributeOptions = array(
            49 => array(
                'label' => 'BLACK',
                'link' => 'http://local.dev.magento2/women/tops-women.html?color=49',
                'custom_style' => '',
            )
        );*/
        /*
        $swatches = array(
            49 => array(
                'swatch_id' => 1,
                'option_id' => 49,
                'store_id' => 0,
                'type' => 1,
                'value' => '#000000',
            )
        );*/
        //$attributeOptions = array();
        $swatches = array();
        $solrFacetData = $this->eavAttribute->getData('facet_data');
        if (is_array($solrFacetData) && count($solrFacetData) > 0) {
            //foreach ($solrFacetData as $optionId => $count) {
            //}
            $swatches = $this->swatchHelper->getSwatchesByOptionsId(array_keys($solrFacetData));
        }
        
        //print_r($attributeOptions);
        
        /*
        $attributeOptionIds = array_keys($attributeOptions);
        $swatches = $this->swatchHelper->getSwatchesByOptionsId($attributeOptionIds);
        */
        
        $data = [
            'attribute_id' => $this->eavAttribute->getId(),
            'attribute_code' => $this->eavAttribute->getAttributeCode(),
            'attribute_label' => $this->eavAttribute->getStoreLabel(),
            'options' => $attributeOptions,
            'swatches' => $swatches,
        ];
        //print_r($data);
        //echo __FILE__.':'.__FUNCTION__;

        return $data;
    }
    
    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        $query = [$attributeCode => $optionId];
        
        $filterQuery = $this->getRequest()->getParam('fq');
        
        $filterKey = $this->filter->getRequestVar();
        
        $params = array();
        
        //If $filterKey exists in $filterQuery
        $filterRequestVar = $this->filter->getRequestVar();
        if (isset($filterQuery[$filterKey])) {
            $params = array(
                $filterRequestVar => $this->utilityHelper->mergeFilterQueryRecusive($filterQuery[$filterKey], array($optionId)),
            );
        } else {
            $params = array(
                $filterRequestVar => array($optionId),
            );
        }
        
        $query = [
            'fq' => $this->utilityHelper->mergeFilterQueryRecusive($filterQuery, $params),
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}
