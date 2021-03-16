<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class AttributeSet
 * @package Plenty\Item\Profile\Config\Source\Product
 */
class AttributeSet implements OptionSourceInterface
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $_productAttributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var array
     */
    private $_options;


    /**
     * Set constructor.
     * @param AttributeSetRepositoryInterface $productAttributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeSetRepositoryInterface $productAttributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_productAttributeSetRepository = $productAttributeSetRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = [['value' => '', 'label' => __('--- select ---')]];
            $attributeSetCollection = $this->getAttributeSetCollection();

            /** @var AttributeSetInterface $attributeSet */
            foreach ($attributeSetCollection as $attributeSet) {
                if (!$attributeSet->getAttributeSetId() || !$attributeSet->getAttributeSetName()) {
                    continue;
                }
                $this->_options[] = [
                    'value' => $attributeSet->getAttributeSetName(),
                    'label' => $attributeSet->getAttributeSetName(),
                ];
            }
        }

        return $this->_options;
    }

    /**
     * @return AttributeSetInterface[]
     */
    private function getAttributeSetCollection()
    {
        // $this->_searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->_searchCriteriaBuilder->create();
        return $this->_productAttributeSetRepository->getList($criteria)->getItems();
    }

}