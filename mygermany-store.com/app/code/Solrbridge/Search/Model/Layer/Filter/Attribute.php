<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\Model\Layer\Filter;

use Solrbridge\Search\Helper\Filter as FilterHelper;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;
    
    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->tagFilter = $tagFilter;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValues = FilterHelper::getParam($this->_requestVar);
        if (empty($attributeValues) || count($attributeValues) < 1) {
            return $this;
        }
        
        $attribute = $this->getAttributeModel();
        
        foreach ($attributeValues as $attributeValue) {
            $label = $this->getOptionText($attributeValue);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $attributeValue));
        }
        //$this->setItems([]); // set items to disable show filtering
        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $facetData = $attribute->getData('facet_data');
        
        $options = $attribute->getFrontend()
            ->getSelectOptions();
        
        $optionsArray = array();
        foreach ($options as $optionData) {
            if (isset($optionData['value'])) {
                $optionsArray[$optionData['value']] = $optionData['label'];
            }
        }
        
        //for select, multiple select attribute $facetValue is $optionId
        foreach ($facetData as $facetValue => $count) {
            $facetLabel = $facetValue;
            if (isset($optionsArray[$facetValue])) {
                $facetLabel = $optionsArray[$facetValue];
            }
            $this->itemDataBuilder->addItemData(
                $this->tagFilter->filter($facetLabel),
                $facetValue,
                $count
            );
        }
        
        return $this->itemDataBuilder->build();
    }
}
