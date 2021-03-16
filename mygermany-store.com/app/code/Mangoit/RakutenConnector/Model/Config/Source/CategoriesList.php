<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model\Config\Source;

class CategoriesList implements \Magento\Framework\Option\ArrayInterface
{
    private $_categoryFactory;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(\Magento\Catalog\Model\CategoryFactory $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Return options array.
     *
     * @param int $store
     *
     * @return array
     */
    public function toOptionArray($store = null)
    {
        $categoriesArr = [];
        $categories = $this->categoryFactory->create()->getCollection();

        foreach ($categories as $category) {
            $category = $this->categoryFactory->create()->load($category->getEntityId());
            if ($category->getName() === 'Root Catalog' || $category->getName() === 'Default Category') {
                continue;
            }
            $categoriesArr[] = ['value' => $category->getEntityId(),'label' => $category->getName()];
        }

        return $categoriesArr;
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        $optionList = $this->toOptionArray();
        $optionArray = [];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }

        return $optionArray;
    }
}
