<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;
use Solrbridge\Search\Helper\Filter as FilterHelper;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

use Solrbridge\Search\Helper\System;

class Category extends \Magento\Catalog\Model\Layer\Filter\Category
{
    protected $categoryFactory;
    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    
    protected $htmlPagerBlock;
    
    protected $request;
    
    protected $utility;
    
    protected $rootCatIds = array();
    
    protected $urlFinder;
    
    protected $categoryUrlPathGenerator;
    
    protected $parsedCategoryIds = array();
    
    protected $categoryUrlRewrites = null;
    
    protected $helper;
    
    protected $filterQuery;
    
    protected $renderAsDropdown = null;
    
    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param CategoryFactory $categoryDataProviderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Solrbridge\Search\Helper\Utility $utilityHelper,
        \Solrbridge\Search\Helper\Data $helper,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $escaper, $categoryDataProviderFactory, $data);
        $this->categoryFactory = $categoryFactory;
        $this->url = $url;
        $this->request = $request;
        $this->utility = $utilityHelper;
        $this->helper = $helper;
        $this->htmlPagerBlock = $htmlPagerBlock;
        
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlFinder = $urlFinder;
        $this->filterQuery = $this->request->getParam('fq');
    }
    
    public function getRenderAsDropdown()
    {
        if (null === $this->renderAsDropdown) {
            $this->renderAsDropdown = System::getHelper()
                                            ->getLayerNavSetting('general/render_category_as_dropdown');
        }
        return $this->renderAsDropdown;
    }
    
    /**
     * Apply category filter to layer
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryIds = FilterHelper::getParam($this->getRequestVar());
        if (!$categoryIds || count($categoryIds) < 1) {
            return $this;
        }
        
        $categoryFacetData = $this->getData(DoctypeHandler::CATEGORY_PATH_KEY);
        
        $categoryData = $this->parseCategoryPathToArray($categoryFacetData);

        foreach ($categoryIds as $categoryId) {
            if (isset($categoryData[$categoryId]) && isset($categoryData[$categoryId]['name'])) {
                //$category = $this->dataProvider->getCategory();
                //$this->getLayer()->getProductCollection()->addCategoryFilter($category);
                $this->getLayer()->getState()->addFilter($this->_createItem($categoryData[$categoryId]['name'], $categoryId));
            }
        }

        return $this;
    }
    
    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $categoryFacetData = $this->getData(DoctypeHandler::CATEGORY_PATH_KEY);
        $this->setData('filter_type', DoctypeHandler::CATEGORY_PATH_KEY);
        $html = $this->parseCategoryPathFacet($categoryFacetData);
        $this->setData('html_data', $html);
        return $categoryFacetData;
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
                    $this->rootCatIds [] = $rootCatId;
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
                    $this->parsedCategoryIds[] = $categoryId;

                    $index++;
                }
            }
        }
        $this->rootCatIds = array_unique($this->rootCatIds);
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

        /*
        $layer = $this->getLayer();
        $_category = $layer->getCurrentCategory();
        $currentCategoryId = $_category->getId();*/
        $currentCategoryId = null;

        if (isset($this->filterQuery['category_facet']) &&
            isset($this->filterQuery['category_id']) &&
            isset($this->filterQuery['category_id'][0])) {
            $currentCategoryId = $this->filterQuery['category_id'][0];
        }

        if ($currentCategoryId) {
            if (isset($categoryArray[$currentCategoryId]['root_cat_id'])) {
                $rootCategoryId = $categoryArray[$currentCategoryId]['root_cat_id'];
            }
        }

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
        
        if ($this->getRenderAsDropdown()) {
            return $this->buildCategoryTreeAsDropdown(0, $treeData);
        }
        
        return $this->buildMenu(0, $treeData);
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
                $html = '<ul class="category-path-menu sf-vertical">';
            } else {
                $html = '<ul>';
            }
            $index = 0;
            foreach ($treeData['parents'][$parentId] as $itemId) {
                //$category = $this->categoryFactory->create()->load($itemId);
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

                $parentClassName = (isset($treeData ['parents'] [$itemId])) ? ' parent' : '';

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
                    if (!$this->helper->isCategoryViewPage()) {
                        $facetUrl = 'javascript:;';
                    }
                    $classNames .= ' empty';
                } else {
                    $formattedCategoryFacet .= '&nbsp;<span>('.$count.')</span>';
                }

                $classNames .= ' ' . $path . $itemId;

                $classNames = trim($classNames);

                if (!$index) {
                    $html .= '<li class="first">' . (($categoryName)?'<a href="'.$facetUrl.'" class="'.$classNames.'">'.$formattedCategoryFacet.'</a>':"");
                } else {
                    $html .= '<li>' . (($categoryName)?'<a href="'.$facetUrl.'" class="'.$classNames.'">'.$formattedCategoryFacet.'</a>':"");
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
        //Category facet url for catalog_category_view
        if ($this->helper->isCategoryViewPage()) {
            return $this->getCategoryUrlRewrite($value);
        }
        
        $filterQuery = $this->request->getParam('fq');
        
        $filterKey = $this->getRequestVar();
        
        $params = array();
        
        //If $filterKey exists in $filterQuery
        if (isset($filterQuery[$filterKey])) {
            $_params = $this->utility->mergeFilterQueryRecusive($filterQuery[$filterKey], array($value));
            $params = array(
                $this->getRequestVar() => $_params
            );
        } else {
            $params = array(
                $this->getRequestVar() => array($value),
            );
        }
        
        $query = [
            'fq' => $this->utility->mergeFilterQueryRecusive($filterQuery, $params),
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        
        return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
    
    protected function getCategoryUrlRewrite($categoryId)
    {
        //load all category url rewites
        if ($this->categoryUrlRewrites === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            
            $result = $this->urlFinder->findAllByData([
                UrlRewrite::ENTITY_ID => $this->parsedCategoryIds,
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            
            if (is_array($result) && count($result) > 0) {
                foreach ($result as $item) {
                    $this->categoryUrlRewrites[$item->getEntityId()] = $item;
                }
            }
        }
        
        if (is_array($this->categoryUrlRewrites) && isset($this->categoryUrlRewrites[$categoryId])) {
            $urlRewrite = $this->categoryUrlRewrites[$categoryId];
            return $this->url->getDirectUrl($urlRewrite->getRequestPath());
        }
        
        return $this->url->getUrl('catalog/category/view', ['id' => $categoryId]);
    }
    
    protected function buildCategoryTreeAsDropdown($parentId, $menuData)
    {
        $html = '<div class="dropdown-facet-wrapper">';
        $html .= '<select class="solrbridge-search-layer-nav-filter-dropdown">';
        $html .= '<option>'.__('All').'</option>';
        $html .= $this->_buildCategoryTreeAsDropdown($parentId, $menuData);
        $html .= '</select></div>';
        return $html;
    }
    
    protected function _buildCategoryTreeAsDropdown($parentId, $menuData, $level = -1, $path='')
    {
        $html = '';
        $level++;
        
        //If found children
        if (isset($menuData['parents'][$parentId]))
        {
            foreach ($menuData['parents'][$parentId] as $itemId)
            {
                $isAnchor = $menuData['items'][$itemId]['is_anchor'];
                $count = $menuData['items'][$itemId]['count'];
                if ($isAnchor) {
                    $count += $this->recursiveCategoryCount($menuData, $itemId);
                }
    
                $categoryName = '';
                if (isset($menuData['items'][$itemId]['name'])) {
                    $categoryName = $menuData['items'][$itemId]['name'];
                }
                $formattedCategoryFacet = $this->facetFormat(trim($categoryName));
                if ($count > 0) {
                    $formattedCategoryFacet .= '&nbsp;<span>('.$count.')</span>';
                }
                
                $facetUrl = $this->getFacetUrl($itemId);
                
                $selectedHtmlAttr = '';
                if ( isset($this->filterQuery['cat']) )
                {
                    if(is_array($this->filterQuery['cat'])) {
                        $matches = array_intersect(array($itemId), $this->filterQuery['cat']);
                        if(count($matches) > 0) {
                            $selectedHtmlAttr = 'selected="selected"';
                        }
                    } else {
                        if($this->filterQuery['cat'] == $itemId) {
                            $selectedHtmlAttr = 'selected="selected"';
                        }
                    }
                }
                
                $html .= '<option '.$selectedHtmlAttr.' value="'.$facetUrl.'">'.$this->_getCategoryPathSpaces($level).$formattedCategoryFacet.'</option>';
                $html .= $this->_buildCategoryTreeAsDropdown($itemId, $menuData, $level, $path);
            }
        }
        
        return $html;
    }
    
    protected function _getCategoryPathSpaces($level = 0)
    {
        $html = '';
        for($i = 1; $i <= $level; $i++) {
            $html .= '--';
        }
        return $html;
    }
}
