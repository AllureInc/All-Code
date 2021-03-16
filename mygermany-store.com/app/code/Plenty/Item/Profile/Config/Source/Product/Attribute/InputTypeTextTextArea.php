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
use Magento\Framework\Api\FilterBuilder;

/**
 * Class InputTypeTextTextArea
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute
 */
class InputTypeTextTextArea implements OptionSourceInterface
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
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var array
     */
    protected $_attributes;


    /**
     * InputTypeTextTextArea constructor.
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @return ProductAttributeInterface[]
     */
    public function getAttributeCollection()
    {
        $inputTextFilter = [
            $this->filterBuilder->setField('frontend_input')
                ->setValue('text')
                ->setConditionType('eq')
                ->create(),
            $this->filterBuilder->setField('frontend_input')
                ->setValue('textarea')
                ->setConditionType('eq')
                ->create()
        ];

        $this->_searchCriteriaBuilder->addFilters($inputTextFilter);
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