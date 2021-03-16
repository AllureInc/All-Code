<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Abstract DataLayer Class
 * For providing category's data
 */
namespace Cnnb\Gtm\DataLayer\CategoryData;

use Magento\Catalog\Model\Category;

/**
 * CategoryAbstract | DataLayer Class
 */
abstract class CategoryAbstract
{
    /**
     * @var CategoryProvider[]
     */
    protected $categoryProviders;

    /**
     * @var array
     */
    private $categoryData = [];

    /**
     * @var Category
     */
    private $category;

    /**
     * @return array
     */
    abstract public function getData();

    /**
     * @return array
     */
    public function getCategoryData()
    {
        return (array) $this->categoryData;
    }

    /**
     * @param array $categoryData
     * @return CategoryAbstract
     */
    public function setCategoryData(array $categoryData)
    {
        $this->categoryData = $categoryData;
        return $this;
    }

    /**
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return CategoryAbstract
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return array|CategoryProvider[]
     */
    public function getCategoryProviders()
    {
        return $this->categoryProviders;
    }
}
