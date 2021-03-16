<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

use Solrbridge\Search\Helper\System;

/**
 * Contact base helper
 */
class Category
{
    protected $_rootCatIds = array();
    
    protected $_categoryPathData = array();
    
    public function setCategoryPathData($data)
    {
        $this->_categoryPathData = $data;
    }
    
    public function toHtml()
    {
        $html = '';
        if (is_array($this->_categoryPathData) && count($this->_categoryPathData) > 0) {
            $html = $this->parseCategoryPathFacet($this->_categoryPathData);
        }
        return $html;
    }
    
    public function parseCategoryPathFacet($categoryPathFaces)
    {
        $categoryArray = $this->parseCategoryPathToArray($categoryPathFaces);
        return $this->renderCategoryHierachy($categoryArray);
    }

    public function parseCategoryPathToArray($categoryPathFaces)
    {
        $returnData = array();

        if (is_array($categoryPathFaces)) {
            foreach ($categoryPathFaces as $categoryPath => $count) {
                $categoryPathArray = $this->pathToArray($categoryPath);

                $rootCatId = (isset($categoryPathArray[0]['id'])) ? $categoryPathArray[0]['id'] : 0;

                if ($rootCatId) {
                    $this->_rootCatIds [] = $rootCatId;
                }

                $index = 0;

                $parents = array();

                foreach ($categoryPathArray as $key => $item) {
                    $categoryName = $item['name'];
                    $categoryId = $item['id'];
                    $position = $item['position'];
                    $isAnchor = $item['is_anchor'];

                    $categoryItem = array(
                            'id' => $categoryId,
                            'name' => $categoryName,
                            'count' => 0,
                            'parent_id' => 0,
                            'position' => $position,
                            'is_anchor' => $isAnchor,
                            'parent_id' => 0,
                            'root_cat_id' => $rootCatId
                    );

                    if ($index == (count($categoryPathArray) - 1)) {
                        $categoryItem['count'] = $count;
                    }

                    if ($key > 0) {
                        $categoryItem['parent_id'] = $categoryPathArray[($key - 1)]['id'];
                    }

                    $parents[] = $categoryId;

                    if (array_key_exists($categoryId, $returnData)) {
                        $returnData[$categoryId]['count'] = ($returnData[$categoryId]['count'] + $categoryItem['count']);
                    } else {
                        $returnData[$categoryId] = $categoryItem;
                    }
                    
                    //collect all category ids
                    $this->_parsedCategoryIds[] = $categoryId;

                    $index++;
                }
            }
        }
        $this->_rootCatIds = array_unique($this->_rootCatIds);
        return $returnData;
    }
    /**
     * Convert string path to array
     * @param string $path
     * @return array
     */
    public function pathToArray($path)
    {
        $chunks = explode('/', $path);
        $result = array();
        for ($i = 0; $i < sizeof($chunks) - 1; $i+=2) {
            //$result[] = array('id' => $chunks[($i+1)], 'name' => $chunks[$i]);
            //CatId format x:y:z, x is category id, y is position, z isAnchor
            $catIdArr = explode(':', $chunks[($i+1)]);
            $result[] = array(
                'id' => $catIdArr[0],
                'position' => $catIdArr[1],
                'name' => $chunks[$i],
                'is_anchor' => isset($catIdArr[2]) ? $catIdArr[2] : 0
            );
        }

        return $result;
    }

    //output a multi-dimensional array as a nested UL

    protected function renderCategoryHierachy($categoryArray)
    {
        $treeData = array(
                'items' => array(),
                'parents' => array()
        );

        $rootCategoryId = 0;

        if ($rootCategoryId > 0) {
            usort($categoryArray, array(get_class($this), 'categoryPositionSort'));
            foreach ($categoryArray as $menuItem) {
                if ($menuItem ['root_cat_id'] == $rootCategoryId) {
                    $treeData ['items'] [$menuItem ['id']] = $menuItem;
                    $treeData ['parents'] [$menuItem ['parent_id']] [] = $menuItem ['id'];
                }
            }
        } else {
            usort($categoryArray, array(get_class($this), 'categoryPositionSort'));
            foreach ($categoryArray as $menuItem) {
                $treeData ['items'] [$menuItem ['id']] = $menuItem;
                $treeData ['parents'] [$menuItem ['parent_id']] [] = $menuItem ['id'];
            }
        }
        return $this->buildMenu(0, $treeData);
    }
    
    protected function isCategoryViewPage()
    {
        return false;
    }
    
    /**
     * Used as callback for usort function to sort array
     * @param array $a
     * @param array $b
     * @return number
     */
    public function categoryPositionSort($a, $b)
    {
        return $a['position'] - $b['position'];
    }
    /**
     * Build category hierachy html
     * @param int $parentId
     * @param array $treeData
     * @return html
     */
    protected function buildMenu($parentId, $treeData, $level = -1, $path = '')
    {
        $html = '';

        $level ++;
        $path .= $parentId . '-';

        if (isset($treeData['parents'][$parentId])) {
            if (!$parentId) {
                $html = '<ul class="items category-path-menu sf-vertical">';
            } else {
                $html = '<ul>';
            }
            $index = 0;
            foreach ($treeData['parents'][$parentId] as $itemId) {
                //$category = $this->_categoryFactory->create()->load($itemId);
                $isAnchor = $treeData['items'][$itemId]['is_anchor'];
                $count = $treeData['items'][$itemId]['count'];
                if ($isAnchor) {
                    $count += $this->recursiveCategoryCount($treeData, $itemId);
                }

                $categoryName = '';
                if (isset($treeData['items'][$itemId]['name'])) {
                    $categoryName = $treeData['items'][$itemId]['name'];
                }

                $facetUrl = $this->getFacetUrl($itemId);

                $parentClassName = (isset($treeData['parents'][$itemId])) ? ' parent' : '';

                $classNames = 'facet-item level-' . $level. ' '. $parentClassName;

                if (isset($this->filterQuery['category_facet']) && isset($this->filterQuery['category_id'])
                        && in_array($categoryName, $this->filterQuery['category_facet'])
                        && in_array($itemId, $this->filterQuery['category_id'])
                ) {
                    $classNames .= ' active';
                    //$facetUrl = $this->getRemoveFacesUrl(array('category', 'category_id'), array($categoryName, $itemId));
                }

                $formattedCategoryFacet = $this->facetFormat(trim($categoryName));

                if ($count < 1) {
                    if (!$this->isCategoryViewPage()) {
                        $facetUrl = 'javascript:;';
                    }
                    $classNames .= ' empty';
                } else {
                    $formattedCategoryFacet .= '&nbsp;<span>('.$count.')</span>';
                }

                $classNames .= ' ' . $path . $itemId;

                $classNames = trim($classNames);

                if (!$index) {
                    $html .= '<li class="first">' . (($categoryName)?'<a href="'.$facetUrl.'" class="'.$classNames.'" data-value="'.$itemId.'" data-key="cat" data-label="'.$categoryName.'" data-title="Category">'.$formattedCategoryFacet.'</a>':"");
                } else {
                    $html .= '<li>' . (($categoryName)?'<a href="'.$facetUrl.'" class="'.$classNames.'" data-value="'.$itemId.'" data-key="cat" data-label="'.$categoryName.'" data-title="Category">'.$formattedCategoryFacet.'</a>':"");
                }
                // find childitems recursively
                $html .= $this->buildMenu($itemId, $treeData, $level, $path);

                $html .= '</li>';
                $index++;
            }
            $html .= '</ul>';
        }

        return $html;
    }
    protected function recursiveCategoryCount($treeData, $parentId)
    {
        $count = 0;
        if (isset($treeData['parents'][$parentId])) {
            foreach ($treeData['parents'][$parentId] as $itemId) {
                $count += $treeData['items'][$itemId]['count'];
                $count += $this->recursiveCategoryCount($treeData, $itemId);
            }
        }
        return $count;
    }
    public function facetFormat($string)
    {
        return urldecode(html_entity_decode($string));
    }
    
    public function getFacetUrl($value)
    {
        return '#';
    }
}
