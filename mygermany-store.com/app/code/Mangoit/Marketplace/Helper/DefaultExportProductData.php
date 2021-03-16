<?php
/**
 * Mango IT Solution.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 * @copyright Copyright (c) Mango IT Solution.
 *
 */
namespace Mangoit\Marketplace\Helper;

class DefaultExportProductData extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getSimpleProductData()
    {
        $header = array ('type','category','name','description','short_description','sku','price','special_price','special_from_date','special_to_date','tax_class_id','is_in_stock','stock','shipping_price_to_mygmbh','mygmbh_shipping_product_length','mygmbh_shipping_product_width','mygmbh_shipping_product_height','product_cat_type','fsk_product_type','product_note','weight','images','meta_title','meta_keyword','meta_description','delivery_days_from','delivery_days_to','lithium','liquid_check','liquid','oil','custom_option');

        $product_data[] = [
            'type' => 'simple',
            'category' => 'Fashion,Fashion Men',
            'name' => 'Enter Product Name',
            'description' => 'Enter Description',
            'short_description' => 'Enter Short Description',
            'sku' => 'Enter Product SKU',
            'price' => '100.0000',
            'special_price' => NULL,
            'special_from_date' => NULL,
            'special_to_date' => NULL,
            'tax_class_id' => 'German Taxable Goods 19%',
            'is_in_stock' => 'In Stock',
            'stock' => 1000,
            'weight' => '1.0000',
            'images' => '4_14_1.jpg,3_23.jpg,2_22_1.jpg,1_27_1.jpg',
            'meta_title' => 'Enter Meta Title',
            'meta_keyword' => 'Enter Meta Keyword',
            'meta_description' => 'Enter Meta Description',
            'shipping_price_to_mygmbh' => '10.0000',
            'mygmbh_shipping_product_length' => '10.0000',
            'mygmbh_shipping_product_width' => '10.0000',
            'mygmbh_shipping_product_height' => '10.0000',
            'product_cat_type' => '12',
            'fsk_product_type' => '0',
            'product_note' => 'Enter Product Note',
            'delivery_days_from' => '2',
            'delivery_days_to' => '4',
            'lithium' => '',
            'liquid_check' => '',
            'liquid' => '',
            'oil' => '',
            'custom_option' => '[{"is_require":"1","title":"Color","type":"drop_down","sort_order":"1","sku":null,"values":[{"title":"White","price":"102.0000","price_type":"fixed","sku":null,"sort_order":"1"},{"title":"Black","price":"104.0000","price_type":"fixed","sku":null,"sort_order":"2"}]}]'
        ];
        return [$header, $product_data];
    }

    public function getConfigurableProductData()
    {
        return [ 
            ['type','category','name','description','short_description','sku','price','special_price','special_from_date','special_to_date','tax_class_id','is_in_stock','stock','shipping_price_to_mygmbh','mygmbh_shipping_product_length','mygmbh_shipping_product_width','mygmbh_shipping_product_height','product_cat_type','fsk_product_type','product_note','weight','images','meta_title','meta_keyword','meta_description','_super_attribute_code','_super_attribute_option','delivery_days_from','delivery_days_to','lithium','liquid_check','liquid','oil','custom_option'],
            [
                [
                  'type' => 'configurable',
                  'category' => 'Deutsche Produkte,Fashion,Fashion Men',
                  'name' => 'Enter Product Name',
                  'description' => 'Enter Product Description',
                  'short_description' => 'Enter Product Short Description',
                  'sku' => 'Enter Product SKU',
                  'price' => '100.0000',
                  'special_price' => NULL,
                  'special_from_date' => NULL,
                  'special_to_date' => NULL,
                  'tax_class_id' => 'German Taxable Goods 19%',
                  'is_in_stock' => 'In Stock',
                  'stock' => 0,
                  'weight' => '0.5600',
                  'images' => '35_3.jpg,34_3.jpg,33_3.jpg,32_4.jpg',
                  'meta_title' => 'Enter Product Meta Title',
                  'meta_keyword' => 'Enter Product Meta Keyword',
                  'meta_description' => 'Enter Product Meta Description',
                  '_super_attribute_code' => 'Size',
                  '_super_attribute_option' => '',
                  'shipping_price_to_mygmbh' => '10.0000',
                  'mygmbh_shipping_product_length' => '10.0000',
                  'mygmbh_shipping_product_width' => '10.0000',
                  'mygmbh_shipping_product_height' => '10.0000',
                  'product_cat_type' => '12',
                  'fsk_product_type' => '0',
                  'product_note' => 'Enter Product Note',
                  'delivery_days_from' => '2',
                  'delivery_days_to' => '5',
                  'lithium' => '',
                  'liquid_check' => '',
                  'liquid' => '',
                  'oil' => '',
                  'custom_option' => '',
                ],
                [
                  'type' => 'simple',
                  'category' => 'Deutsche Produkte,Fashion,Fashion Men',
                  'name' => 'Enter Product Name',
                  'description' => 'Enter Product Description',
                  'short_description' => 'Enter Product Short Description',
                  'sku' => 'Enter Product SKU',
                  'price' => '100.0000',
                  'special_price' => NULL,
                  'special_from_date' => NULL,
                  'special_to_date' => NULL,
                  'tax_class_id' => 'German Taxable Goods 19%',
                  /*'is_in_stock' => true,*/
                  'is_in_stock' => 'In Stock',
                  'stock' => 100,
                  'weight' => '0.5600',
                  'images' => '35_4.jpg,34_4.jpg,33_4.jpg,32_1_1.jpg',
                  'meta_title' => 'Enter Product Meta Title',
                  'meta_keyword' => 'Enter Product Meta Keyword',
                  'meta_description' => 'Enter Product Meta Description',
                  '_super_attribute_code' => 'Size',
                  '_super_attribute_option' => 'L',
                ],
                [
                  'type' => 'simple',
                  'category' => 'Deutsche Produkte,Fashion,Fashion Men',
                  'name' => 'Enter Product Name',
                  'description' => 'Enter Product Description',
                  'short_description' => 'Enter Product Short Description',
                  'sku' => 'Enter Product SKU',
                  'price' => '100.0000',
                  'special_price' => NULL,
                  'special_from_date' => NULL,
                  'special_to_date' => NULL,
                  'tax_class_id' => 'German Taxable Goods 19%',
                  /*'is_in_stock' => true,*/
                  'is_in_stock' => 'In Stock',
                  'stock' => 100,
                  'weight' => '0.5600',
                  'images' => '35_1_1.jpg,34_1_1.jpg,33_1_1.jpg,32_2_1.jpg',
                  'meta_title' => 'Enter Product Meta Title',
                  'meta_keyword' => 'Enter Product Meta Keyword',
                  'meta_description' => 'Enter Product Meta Description',
                  '_super_attribute_code' => 'Size',
                  '_super_attribute_option' => 'M',
                ],
                [
                  'type' => 'simple',
                  'category' => 'Deutsche Produkte,Fashion,Fashion Men',
                  'name' => 'Enter Product Name',
                  'description' => 'Enter Product Description',
                  'short_description' => 'Enter Product Short Description',
                  'sku' => 'Enter Product SKU',
                  'price' => '100.0000',
                  'special_price' => NULL,
                  'special_from_date' => NULL,
                  'special_to_date' => NULL,
                  'tax_class_id' => 'German Taxable Goods 19%',
                  /*'is_in_stock' => true,*/
                  'is_in_stock' => 'In Stock',
                  'stock' => 100,
                  'weight' => '0.5600',
                  'images' => '35_2_1.jpg,34_2_1.jpg,33_2_1.jpg,32_3_1.jpg',
                  'meta_title' => 'Enter Product Meta Title',
                  'meta_keyword' => 'Enter Product Meta Keyword',
                  'meta_description' => 'Enter Product Meta Description',
                  '_super_attribute_code' => 'Size',
                  '_super_attribute_option' => 'S',
                ],
            ],
        ];
    }
}