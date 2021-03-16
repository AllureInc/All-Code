<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * DataLayer Class
 * For providing category's data
 */
namespace Cnnb\Gtm\DataLayer\CategoryData;

/**
 * CategoryProvider | DataLayer Class
 */
class CategoryProvider extends CategoryAbstract
{
    /**
     * @param array $categoryProviders
     */
    public function __construct(
        array $categoryProviders = []
    ) {
        $this->categoryProviders = $categoryProviders;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data =  $this->getcategoryData();
        $arraysToMerge = [];

        /** @var CategoryProvider $categoryProvider */
        foreach ($this->getCategoryProviders() as $categoryProvider) {
            $categoryProvider->setCategory($this->getCategory())->setCategoryData($data);
            $arraysToMerge[] = $categoryProvider->getData();
        }

        return empty($arraysToMerge) ? $data : array_merge($data, ...$arraysToMerge);
    }
}
