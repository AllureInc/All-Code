<?php 
namespace Mangoit\Sellerapi\Api;
 
 
interface SellersAttributesInterface {


	/**
	 * GET for Post api
	 * @param int $param
	 * @return string
	 */
	
	public function getSellerAttributes($seller_id); 
}