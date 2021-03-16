<?php
namespace Mangoit\FskRestricted\Block\Filterproducts;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class LatestList extends \Smartwave\Filterproducts\Block\Home\LatestList 
{
    /*protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Mangoit_FskRestricted::small_list.phtml');
        }

        return $this;
    }*/

    protected function _getProductCollection() {
        return $this->getProducts();
    }
    
    public function getProducts() {
        $count = $this->getProductCount();                       
        $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);
        
        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }

        $category = $this->categoryRepository->get($category_id);

        /* custom code*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $restrictedIds = $objectManager->create('Mangoit\FskRestricted\Block\Filterproducts\FeaturedList')->getRestrictedProductData();
        if (!empty($restrictedIds)) {
            $collection->addFieldToFilter('entity_id', array('nin'=> $restrictedIds));
        }
        /* custom code ends*/

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
        
        $collection->getSelect()
                ->order('created_at','desc')
                ->limit($count);
                
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
