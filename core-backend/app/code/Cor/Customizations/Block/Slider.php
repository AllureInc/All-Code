<?php
/**
 * Copyright © 2018 Cor . All rights reserved.
 * 
 */
namespace Cor\Customizations\Block;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Slider extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var Configurable
     */
    protected $configurableProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_priceHelper;

    public function __construct( 
        \Magento\Framework\View\Element\Template\Context $context,       
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Configurable $configurableProduct,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ){
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_priceHelper = $priceHelper;
        $this->_storeManager = $storeManager;
        $this->_imageHelper = $imageHelper;
        $this->configurableProduct = $configurableProduct;
        $this->pricingHelper = $pricingHelper;
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('cor_use_in_slider', 1);

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * Get category
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Model\CategoryFactory array
     */
    public function getCategory($categoryId) 
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    /**
     * Get category products collection
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryProducts($categoryId) 
    {
        $products = $this->getCategory($categoryId)->getProductCollection();
        $products->addAttributeToSelect('*');
        return $products;
    }

    /**
     * Get category collection
     *
     * @return product media url
     */
    public function getProdutBaseUrl(){
        $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        return $mediaUrl.'catalog/product';
    }

    /**
     * Get category collection
     *
     * @param int $price
     * @return formatted price
     */
    public function getPriceFormatted($price) {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * Get configurable product price range
     *
     * @param $product
     * @return string
     */
    public function getPriceRange($product)
    {
        $childProductPrice = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);
        foreach($childProducts as $child) {
            $price = number_format($child->getPrice(), 2, '.', '');
            $finalPrice = number_format($child->getFinalPrice(), 2, '.', '');
            if($price == $finalPrice) {
                $childProductPrice[] = $price;
            } else if($finalPrice < $price) {
                $childProductPrice[] = $finalPrice;
            }
        }

        $max = $this->pricingHelper->currencyByStore(max($childProductPrice));
        $min = $this->pricingHelper->currencyByStore(min($childProductPrice));
        if($min==$max){
            return $this->getPriceRender($product, "$min", '');
        } else {
            return $this->getPriceRender($product, "$min - $max", '');
        }
    }

    /**
     * Price renderer
     *
     * @param $product
     * @param $price
     * @return mixed
     */
    protected function getPriceRender($product, $price, $text='')
    {
        return $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Cor_Customizations::product/price/range/price.phtml')
            ->setData('price_id', 'product-price-'.$product->getId())
            ->setData('display_label', $text)
            ->setData('product_id', $product->getId())
            ->setData('display_value', $price)->toHtml();
    }

    /**
     * Placeholder Image
     * @return url
     */
    public function getPlaceholderImageUrl()
    {
        return $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail');
    }
}
