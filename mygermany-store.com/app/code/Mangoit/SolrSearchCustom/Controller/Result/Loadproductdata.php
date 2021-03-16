<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\SolrSearchCustom\Controller\Result;

use Magento\Framework\App\Action\Context;

class Loadproductdata extends \Solrbridge\Search\Controller\Result\Loadproductdata
{
    public function execute()
    {
        $productIds = $this->getRequest()->getParam('productids');
        $responseData = array();
        if (!empty($productIds)) {
            $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
            $productIds = explode(',', $productIds);
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                /*@TOBEDELETE-if ($product->getTypeId() == 'configurable') {
                    //echo $product->getPriceInfo()->getPrice('lowest_price');
                    /*
                    foreach ($product->getPriceInfo()->getPrices() as $priceObject) {
                        echo get_class($priceObject);
                    }*
                    //die();
                }*/
                    
                    //echo get_class($product->getPriceInfo()->getPrice('special_price'));die();
                
                $price = floatval($product->getPrice());
                $finalPrice = floatval($product->getFinalPrice());
                $minimalPrice = floatval($product->getMinimalPrice());

                // Code added for universal tax application.
                $rate = 19;
                $taxAttribute = $product->getCustomAttribute('tax_class_id');
                if($taxAttribute) {
                    $productRateId = $taxAttribute->getValue();
                    $taxCalculation = $this->_objectManager->get('Magento\Tax\Api\TaxCalculationInterface');
                    if($productRateId) {
                        $rate = $taxCalculation->getCalculatedRate($productRateId);
                    }
                }

                $priceTax = (($price*$rate)/100);
                $finalPriceTax = (($finalPrice*$rate)/100);
                $minimalPriceTax = (($minimalPrice*$rate)/100);

                $price = ($price + $priceTax);
                if($product->getTypeId() != 'configurable') {
                    $finalPrice = ($finalPrice + $finalPriceTax);
                }
                $minimalPrice = ($minimalPrice + $minimalPriceTax);
                // END Code added for universal tax application.

                //Has special price
                $showSpecialPrice = 0;
                if ($price > 0 && $finalPrice < $price) {
                    if ($product->getPriceInfo()->getPrice('special_price')->isScopeDateInInterval()) {
                        $showSpecialPrice = 1;
                    }
                } else {
                    //logic here
                }
                $responseData[$productId] = array(
                    'price' => $priceHelper->currency($price, true, false),
                    'minimal_price' => $priceHelper->currency($minimalPrice, true, false),
                    'final_price' => $priceHelper->currency($finalPrice, true, false),
                    'thumb' => $this->getProductThumb($product),
                    'show_special_price' => $showSpecialPrice
                );
            }
        }
        echo json_encode($responseData, true);
        exit();
    }
}
