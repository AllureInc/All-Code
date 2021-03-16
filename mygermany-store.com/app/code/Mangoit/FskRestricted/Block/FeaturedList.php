<?php

namespace Mangoit\FskRestricted\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class FeaturedList extends \Smartwave\Filterproducts\Block\Home\FeaturedList {

    public function _getProductCollection() {
        return $this->getProducts();
    }
    
    public function getProducts() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $count = $this->getProductCount();                       
        $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        // $newCollection = [];
        // $secondCollectionsProductIds = [];
        // $restrictedHelper = $objectManager->create('Mangoit\FskRestricted\Helper\Data');
        // $countryName = $restrictedHelper->getCurrentCountry();      
        // $productModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        // $productModelCollection = $productModel->getCollection();
        // foreach ($productModelCollection as $item) {
        //     $checkContryArr =  explode(",", $item['restricted_countries']);
        //     if (in_array($countryName, $checkContryArr)) {
        //         array_push($secondCollectionsProductIds, $item['product_id']);
        //     }
        // }

        // $this->_collection->addAttributeToFilter('entity_id', ['nin' => $secondCollectionsProductIds]);
        // $collection = $this->_collection;

        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);
        

        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }
       
        $category = $this->categoryRepository->get($category_id);
        if(isset($category) && $category) {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', 1, 'left')
                ->addCategoryFilter($category)
                ->addAttributeToSort('created_at','desc');

        } else {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', 1, 'left')
                ->addAttributeToSort('created_at','desc');
        }
          // echo "<pre>";
        // print_r($this->_collection->getData());
        
        $collection->getSelect()
                ->order('created_at','desc')
                ->limit($count);
        // echo "123";
        // echo "<pre>";
        // print_r($collection->getData());
        // die("1121");
        
        // die();
        return $collection;
    }

    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    public function getProductCount() {
        $limit = $this->getData("product_count");
        if(!$limit)
            $limit = 10;
        return $limit;
    }
}
