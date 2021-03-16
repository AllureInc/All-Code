<?php 
namespace Mangoit\Sellerapi\Api;
 
 
interface SellerProductInterface {


	/**
	 * GET for Post api
	 * @param int $param
	 * @param mixed $product
	 * @return string
	 */
	
	public function addSellerProduct($seller_id, \Magento\Catalog\Api\Data\ProductInterface  $product); 
}