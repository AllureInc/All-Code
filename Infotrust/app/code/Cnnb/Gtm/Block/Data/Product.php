<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing product's data
 */
namespace Cnnb\Gtm\Block\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Data;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\DataLayer\ProductData\ProductProvider as ProductProvider;
use Cnnb\Gtm\Helper\Product as ProductHelper;
use Cnnb\Gtm\Model\DataLayerEvent;

class Product extends AbstractProduct
{
    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogHelper = null;

    /**
     * @var ProductHelper
     */
    private $_productHelper;

    /**
     * @var ProductProvider
     */
    private $_productProvider;

    /**
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var Rating
     */
    protected $_ratingFactory;

    /**
     * @param  Context  $context
     * @param  ProductHelper  $productHelper
     * @param  ProductProvider  $productProvider
     * @param  array  $data
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper,
        ProductProvider $productProvider,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\Rating $ratingFactory,
        array $data = []
    ) {
        $this->_catalogHelper = $context->getCatalogHelper();
        parent::__construct($context, $data);
        $this->_productHelper = $productHelper;
        $this->_productProvider = $productProvider;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
    }

    /**
     * Add product data to datalayer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();
        if ($product = $this->getProduct()) {

            $attributes = $product->getAttributes();
            $attr_array = [];
            foreach ($attributes as $attribute) {
                $attr_array[$attribute->getAttributeCode()] = [
                    'code' => $attribute->getAttributeCode(),
                    'value' => $product->getData($attribute->getAttributeCode())
                ];
            }
            $product->setData('attr_data', $attr_array);
            $variant_ids = [];
            if ($product->getTypeId() == 'configurable') {
                    $child_product = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($child_product as $child) {
                    $variant_ids[] = $child->getId();
                }
                    $product->setData('variant_ids', $variant_ids);
            }
            $productData = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'parent_sku' => $product->getData('sku'),
                'product_type' => $product->getTypeId(),
                'name' => $product->getName(),
                'price' => $this->getPrice(),
                'variant' => ($product->getData('variant_ids')) ? implode(',', $product->getData('variant_ids')) : '',
                'attribute_set_id' => $product->getAttributeSetId(),
                'path' => implode(" > ", $this->getBreadCrumbPath()),
                'category' => $this->getProductCategoryName(),
                'image_url' => $this->_productHelper->getImageUrl($product),
                'brand' => $product->getData('brand'),
                'attributes' => $product->getData('attr_data'),
                'ratings' => $this->getRating($product->getId())
            ];
            $productData = $this->_productProvider->setProduct($product)->setProductData($productData)->getData();
            $data = [
                'event' => DataLayerEvent::PRODUCT_PAGE_EVENT,
                'product' => $productData
            ];
            $tm->addVariable('list', 'detail');
            $tm->addCustomDataLayerByEvent(DataLayerEvent::PRODUCT_PAGE_EVENT, $data);
        }

        return $this;
    }

    /**
     * @return product rating
     */
    public function getRating($productId)
    {
        $_ratingSummary = $this->_ratingFactory->getEntitySummary($productId);
        $ratingCollection = $this->_reviewFactory->create()->getResourceCollection()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product', $productId);
        $review_count = count($ratingCollection);
        return $review_count;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();
        $price = 0;

        /** @var $product ProductInterface */
        if ($product = $this->getProduct()) {
            $price = !$product->getSpecialPrice() ? $product->getPrice() : $product->getSpecialPrice();
        }

        return $tm->formatPrice($price);
    }

    /**
     * Get category name from breadcrumb
     *
     * @return string
     */
    protected function getProductCategoryName()
    {
        $categoryName = '';

        try {
            $categoryArray = $this->getBreadCrumbPath();

            if (count($categoryArray) > 1) {
                end($categoryArray);
                $categoryName = prev($categoryArray);
            } elseif ($this->getProduct()) {
                $category = $this->getProduct()->getCategoryCollection()->addAttributeToSelect('name')->getFirstItem();
                $categoryName = ($category) ? $category->getName() : '';
            }
        } catch (Exception $e) {
            $categoryName = '';
        }

        return $categoryName;
    }

    /**
     * Get bread crumb path
     *
     * @return array
     */
    protected function getBreadCrumbPath()
    {
        $titleArray = [];
        $breadCrumbs = $this->_catalogHelper->getBreadcrumbPath();

        foreach ($breadCrumbs as $breadCrumb) {
            $titleArray[] = $breadCrumb['label'];
        }

        return $titleArray;
    }
}
