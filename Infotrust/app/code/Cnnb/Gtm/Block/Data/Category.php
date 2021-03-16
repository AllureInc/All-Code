<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing category's data
 */
namespace Cnnb\Gtm\Block\Data;

use Magento\Catalog\Model\Category as ProductCategory;
use Magento\Catalog\Helper\Data;
use Cnnb\Gtm\Helper\Data as Helper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\DataLayer\CategoryData\CategoryProvider;
use Cnnb\Gtm\Model\DataLayerEvent;
use Magento\Review\Model\Review;

class Category extends Template
{
    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CategoryProvider
     */
    private $categoryProvider;

    /**
     * @var helper
     */
    private $_helper;

    /**
     * @var Review
     */
    private $_review;

    /**
     * @var ReviewHelper
     */
    private $reviewHelper;

    /**
     * @param  Context  $context
     * @param  Registry  $registry
     * @param  Data  $catalogData
     * @param  CategoryProvider  $categoryProvider
     * @param  array  $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $catalogData,
        Helper $helper,
        CategoryProvider $categoryProvider,
        Review $review,
        array $data = []
    ) {
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->categoryProvider = $categoryProvider;
        $this->_helper = $helper;
        $this->_review = $review;
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * Add category data to datalayer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();

        /** @var $category ProductCategory */
        $category = $this->getCurrentCategory();

        if ($category) {
            $categoryData = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];

            $categoryData = $this->categoryProvider
                ->setCategory($category)
                ->setCategoryData($categoryData)
                ->getData();

            $productCollection = $category->getProductCollection()->addAttributeToSelect('*');
            $products = [];
            
            foreach ($productCollection as $product) {
               
                $variant_ids = [];
                if ($product->getTypeId() == 'configurable') {
                    $child_product = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($child_product as $child) {
                        $variant_ids[] = $child->getId();
                    }
                    $product->setData('variant_ids', $variant_ids);
                }

                $product->setData('ratings', $this->_helper->getRating($product->getEntityId()));
                
                $products[] = $product->getData();
            }

            $data = [
                'event' => DataLayerEvent::CATEGORY_PAGE_EVENT,
                'eventCategory' => 'Ecommerce',
                'eventLabel' => 'Product List Page',
                'category' => $categoryData,
                'products' => $products
            ];

            $tm->addVariable('list', 'category');
            $tm->addCustomDataLayerByEvent(DataLayerEvent::CATEGORY_PAGE_EVENT, $data);
        }

        return $this;
    }
}
