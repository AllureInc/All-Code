<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Category;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Magento
 * @package Plenty\Item\Profile\Config\Source\Category
 */
class Magento implements OptionSourceInterface
{
    /**
     * @var CategoryListInterface
     */
    private $_categoryListRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $_options;

    /**
     * Magento constructor.
     * @param CategoryListInterface $categoryListRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CategoryListInterface $categoryListRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_categoryListRepository = $categoryListRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return CategoryInterface[]
     */
    public function getRootCategories()
    {
        $this->_searchCriteriaBuilder->addFilter('level', 1);
        $criteria = $this->_searchCriteriaBuilder->create();
        return $this->_categoryListRepository->getList($criteria)->getItems();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = [];
            /** @var CategoryInterface $category */
            foreach ($this->getRootCategories() as $category) {
                $this->_options[] = [
                    'value' => $category->getId(),
                    'label' => $category->getName(),
                ];
            }
        }

        return $this->_options;
    }
}