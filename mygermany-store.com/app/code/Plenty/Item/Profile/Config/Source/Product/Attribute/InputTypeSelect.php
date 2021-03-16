<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Attribute;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class InputTypeSelect
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute
 */
class InputTypeSelect implements OptionSourceInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $_productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $_attributes;


    /**
     * InputTypeSelect constructor.
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return ProductAttributeInterface[]
     */
    public function getAttributeCollection()
    {
        $this->_searchCriteriaBuilder->addFilter('frontend_input', 'select');
        $criteria = $this->_searchCriteriaBuilder->create();
        return $this->_productAttributeRepository->getList($criteria)->getItems();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_attributes) {
            $this->_attributes = [['value' => '', 'label' => __('--- select ---')]];
            $attributes = $this->getAttributeCollection();
            /** @var ProductAttributeInterface $attribute */
            foreach ($attributes as $attribute) {
                $this->_attributes[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getDefaultFrontendLabel(),
                ];
            }
        }

        return $this->_attributes;
    }
}