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
 * Class Price
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute
 */
class Price implements OptionSourceInterface
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
    protected $_priceAttributes;


    /**
     * Price constructor.
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
    public function getPriceAttributeCollection()
    {
        $this->_searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->_searchCriteriaBuilder->create();
        return $this->_productAttributeRepository->getList($criteria)->getItems();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_priceAttributes) {
            $this->_priceAttributes = [['value' => '', 'label' => __('--- select ---')]];
            $priceAttributes = $this->getPriceAttributeCollection();
            /** @var ProductAttributeInterface $priceAttribute */
            foreach ($priceAttributes as $priceAttribute) {
                if (!$priceAttribute->getAttributeCode() || !$priceAttribute->getDefaultFrontendLabel()) {
                    continue;
                }
                $this->_priceAttributes[] = [
                    'value' => $priceAttribute->getAttributeCode(),
                    'label' => $priceAttribute->getDefaultFrontendLabel(),
                ];
            }
        }

        return $this->_priceAttributes;
    }
}