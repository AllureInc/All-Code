<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Block File
 * retrieves products associated with particular event and artist.
 */
namespace Cor\MerchandiseHandling\Block\Adminhtml;
class ArtistProducts extends \Magento\Backend\Block\Template
{
    /**
     * Method to retrieve artists products collection.
     * @return productslist
     */
    public function getProductsCollection() {
        $artist_id = $this->getRequest()->getParam('artist_id');
        $event_id = $this->getRequest()->getParam('event_id');
        $eventFilter = [['attribute' => 'cor_events', 'finset' => $event_id]];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productModel = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productFactory = $productModel->create();
        $productFactory->addAttributeToSelect('entity_id');
        $productFactory->addAttributeToFilter($eventFilter);
        $productFactory->addAttributeToFilter('cor_artist', $artist_id);
        $productFactory->addAttributeToFilter('visibility', ['neq' => 1]);

        return $productFactory;
    }
    /**
     * Method to retrieve artists configurable child products collection.
     * @return childproductslist
     */
    public function getConfigurableChildProducts($product) {
        $productAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $childrens = $product->getTypeInstance()->getUsedProducts($product);
        $childProducts = array();
        $headerOptions = array();

        $attributeOptions = array();
        foreach ($productAttributes as $productAttribute) {
            $attrOpts = array();
            foreach ($productAttribute['values'] as $attrValues) {
                $attrOpts[$attrValues['value_index']] = $attrValues['label'];
            }
            $attributeOptions[$productAttribute['attribute_code']]['options'] = $attrOpts;
            $attributeOptions[$productAttribute['attribute_code']]['label'] = $productAttribute['label'];
        }
        
        foreach ($childrens as $child){
            $data = $child->getData();
            $childProductsOptions = array();
            foreach ($attributeOptions as $attributeCode => $attribute) {
                if (isset($data[$attributeCode])) {
                    $headerOptions[$attributeCode] = $attribute['label'];
                    $childProductsOptions[$attributeCode] = $attribute['options'][$data[$attributeCode]];
                }
            }
            $childProducts[$data['sku']]['options'] = $childProductsOptions;
            $childProducts[$data['sku']]['product'] = $child;
        }

        $response['header_options'] = $headerOptions;
        $response['child_products'] = $childProducts;
        return $response;
    }
    
    /**
     * Method to retrieve child bundle products collection.
     *
     * @return Bundle product array
     */
    public function getBundleProducts($product) {
        $childProducts = array();
        $collection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
        foreach ($collection as $data) {
            $childProduct =  $this->getChildProduct($data['sku']);
            $childProducts[] = $childProduct;
        }
        return $childProducts;
    }
 
    /**
     * Method to retrieve group child products collection
     *
     * @return Group child product array
     */
    public function getGroupProducts($product) {
        $childProducts = [];
        $collection = $product->getTypeInstance(true)->getAssociatedProducts($product);
        foreach ($collection as $data) {
            $childProduct =  $this->getChildProduct($data->getSku());
            $childProducts[] = $childProduct;
        }
        return $childProducts;
    }

    /**
     * Method to retrieve merchandise details for artist and event selected.
     * @return merchandiseList
     */
    public function getMerchandise() {
        $artist_id = $this->getRequest()->getParam('artist_id');
        $event_id = $this->getRequest()->getParam('event_id');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $model = $objectManager->create('\Cor\MerchandiseHandling\Model\Merchandise');
        $collection = $model->getCollection()->addFieldToFilter('artist_id', $artist_id)->addFieldToFilter('event_id', $event_id);
        $data = array();
        if (!empty($collection->getData())) {
            foreach ($collection->getData() as $item) {
                $key = $item['event_id'].'_'.$item['artist_id'].'_'.$item['product_id'].'_'.$item['product_parent_id'];
                $data[$key] = $item;
            }
        }
        return $data;
    }

    /**
     * Method to retrieve artists products collection through id.
     *
     * @return artistlist
     */
    public function getProducts($id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Magento\Catalog\Model\Product')->load($id);
        return $model;
    }

    /**
     * Method to retrieve configurable child products collection through sku.
     *
     * @return Configurable product data array
     */
    public function getChildProduct($sku) {
        $data = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $productData = $productRepository->get($sku);
        //$data = ['id'=> $productData->getEntityId(), 'name'=> $productData->getName(), 'price'=> $productData->getPrice()];
        return $productData;
    }

    /**
     * Method to retrieve base currency symbol.
     *
     * @return Currency Symbol
     */
    public function getCurrencySymbol()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }
}
