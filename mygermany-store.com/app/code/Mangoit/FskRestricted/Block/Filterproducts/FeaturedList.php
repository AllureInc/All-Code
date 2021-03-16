<?php
namespace Mangoit\FskRestricted\Block\Filterproducts;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class FeaturedList extends \Smartwave\Filterproducts\Block\Home\FeaturedList
{
    /*protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Mangoit_FskRestricted::owl_list.phtml');
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
        $restrictedIds = $this->getRestrictedProductData();
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
                ->addAttributeToFilter('sw_featured', 1, 'left')
                ->addCategoryFilter($category);
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
                ->addAttributeToFilter('sw_featured', 1, 'left');
        }        

        $collection->getSelect()
            ->order('rand()')
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

     /* Custom Code */
    public function getRestrictedProductData()
    {   
        $restrictedproductArray = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $restrictedHelper = $objectManager->create('Mangoit\FskRestricted\Helper\Data');
        $countryName = $restrictedHelper->getCurrentCountry();
        $productCollection = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct')->getCollection();
        foreach ($productCollection as $item) {
            if (
                in_array($countryName, 
                    explode(',', $item['restricted_countries'])
                )
            ) {
                $restrictedproductArray[] = $item['product_id'];
            }
        }

        return array_unique($restrictedproductArray);        
    }
    /* Custom Code ends*/
}
