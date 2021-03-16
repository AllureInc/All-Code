<?php 
namespace Mangoit\Sellerapi\Api;
 
 
interface SellerCategoriesInterface {


	/**
	 * GET for Post api
	 * @param string $param
	 * @return string
	 */
	
	public function getData($seller_id);
}