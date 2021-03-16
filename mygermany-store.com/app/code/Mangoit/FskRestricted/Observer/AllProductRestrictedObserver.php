<?php

namespace Mangoit\FskRestricted\Observer;

use Magento\Framework\Event\ObserverInterface;

class AllProductRestrictedObserver implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	die("123");
        $_product = $observer->getProduct();  // you will get product object
        // $productId = $observer->getProduct()->getId();
        // if ($observer->getProduct()->getCustomAttribute('restricted_product')) {
	       //  $productName = $_product->getName();
	       //  $status = $_product->getStatus();
	       //  if ($status != 1) {
	       //  	$productStatus = 'Disabled';
	       //  } else {
	       //  	$productStatus = 'Enabled';
	       //  }
	       //  $webkulProductModel = $objectManager->create('Webkul\Marketplace\Model\Product');
	       //  if ( !empty($webkulProductModel->load($productId, 'mageproduct_id')->getData()) ) {
	       //  	 $productCollection = $webkulProductModel->load($productId, 'mageproduct_id');
	       //  	$sellerID = $productCollection->getSellerId();
	       //  	$sellerModel = $objectManager->create('Magento\Customer\Model\Customer')->load($sellerID, 'entity_id');
	       //  	$vendorFirstName = $sellerModel->getFirstname();
	       //  	$vendorLastName = $sellerModel->getLastname();
	       //  	$vendorName = $vendorFirstName.' '.$vendorLastName;
	       //  } else {
	       //  	$vendorName = 'Admin';
	       //  }
	       //  $restrictedProductModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
	       //  if ( !empty($restrictedProductModel->load($productId, 'product_id')->getData()) ) {
	       //  	$model = $restrictedProductModel->load($productId, 'product_id');
	       //  	$model->setProductId($productId);
	       //  	$model->setProductName($productName);
	       //      $model->setProductStatus($productStatus);
	       //      $model->setVendorName($vendorName);
	       //      $model->save();
	       //  } else {
		      //   $restrictedProductModel->setProductId($productId);
		      //   $restrictedProductModel->setProductName($productName);
		      //   $restrictedProductModel->setProductStatus($productStatus);
		      //   $restrictedProductModel->setVendorName($vendorName);
		      //   $restrictedProductModel->save();	        	
	       //  }
        // }
    }   
}