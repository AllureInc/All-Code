<?php

namespace Mangoit\FskRestricted\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductRestrictedObserver implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Object Manager 
        $_product = $observer->getProduct();  // you will get product object
        $productId = $observer->getProduct()->getId();
        $categoryArray = [];
        $modelCategoryArray = [];
        // If Admin set the value of  restricted_product yes.
        if ($observer->getProduct()->getCustomAttribute('restricted_product')) {
        	$restricted = $observer->getProduct()->getCustomAttribute('restricted_product')->getValue();
	        if ($restricted != 0) {
	        	foreach ($_product->getCategoryIds() as $value) {
	        		$categoryModel =  $objectManager->create('Magento\Catalog\Model\Category')->load($value);
	        		array_push($categoryArray, array('id' => $value, 'name' => $categoryModel->getName() ));
	        	}

		        $productName = $_product->getName();
		        $status = $_product->getStatus();
		        if ($status != 1) {
		        	$productStatus = 'Disabled';
		        } else {
		        	$productStatus = 'Enabled';
		        }
		        // For Vendor Name 
		        $webkulProductModel = $objectManager->create('Webkul\Marketplace\Model\Product');
		        if ( !empty($webkulProductModel->load($productId, 'mageproduct_id')->getData()) ) {
		        	 $productCollection = $webkulProductModel->load($productId, 'mageproduct_id');
		        	$sellerID = $productCollection->getSellerId();
		        	$sellerModel = $objectManager->create('Magento\Customer\Model\Customer')->load($sellerID, 'entity_id');
		        	$vendorFirstName = $sellerModel->getFirstname();
		        	$vendorLastName = $sellerModel->getLastname();
		        	$vendorName = $vendorFirstName.' '.$vendorLastName;
		        } else {
		        	$vendorName = 'Admin';
		        }
		        // vendor_restricted_products (table)

		        // $restrictedProductModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
		       
		        foreach ($categoryArray as $key => $value) {
			        $restrictedProductModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
				    $modelCollection = $restrictedProductModel->getCollection();
			        $modelCollection->addFieldToFilter('product_id', array('eq' => $productId));
		        	$modelCollection->addFieldToFilter('category_id', array('eq' => $value['id']));
		        	if (!empty($modelCollection->getData() )) {
		        		foreach ($modelCollection as $item) {
		        			$restrictedProductModel->load($item->getId());
		        			$restrictedProductModel->setProductStatus($productStatus);
		        			$restrictedProductModel->save();
				            $restrictedProductModel->unsetData();
		        		}
		        	} else {
		        		$restrictedProductModel->setProductId($productId);
				        $restrictedProductModel->setCategoryId($value['id']);
				        $restrictedProductModel->setCategoryName($value['name']);
				        $restrictedProductModel->setProductName($productName);
				        $restrictedProductModel->setProductStatus($productStatus);
				        $restrictedProductModel->setVendorName($vendorName);
				        $restrictedProductModel->save();
				        $restrictedProductModel->unsetData();
		        	}
		        }
	        }
        }
    }   
}