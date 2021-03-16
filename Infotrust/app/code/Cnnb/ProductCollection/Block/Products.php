<?php
namespace Cnnb\ProductCollection\Block;

class Products extends \Magento\Framework\View\Element\Template {

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**          
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory                 $productCollection
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
    ) {
        $this->productCollection = $productCollection;
    }
    
    public function getProductColl($attr) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Your text message');
        
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', ['eq' => 'bundle']);
        foreach ($collection as $product) {
            print_r($product->getData());
            echo '</br>';
        }
        // $attributeSetArray = $this->options->toOptionArray();
        // $attrSetIds = [];
        // foreach ($attributeSetArray as $key => $value) {
        //     $attributes = $this->attributeManagement->getAttributes(\Magento\Catalog\Model\Product::ENTITY, $value['value']); // Get all attribute using attribute set id.
        //     foreach ($attributes as $attribute) {
        //         if ($attribute->getAttributeId() == $attrId) { // $attrId is your attribute id which you want to check that exist in attribute set.
        //             $attrSetIds[] = $value['value'];
        //         }
        //     }
        // }
        // $collection = $this->collectionFactory->create();
        // $collection->addFieldToSelect('*');
        // $collection->addFieldToFilter('attribute_set_id', ['in' => $attrSetIds]); // Return collection based on attribute set ids. It means your attribute exist in product.
        return $collection;
    }
}