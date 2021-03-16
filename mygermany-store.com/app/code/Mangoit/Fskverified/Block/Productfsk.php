<?php
namespace Mangoit\Fskverified\Block;

/**
*
*@category Praveen
* @package Praveen\Register
*/
class Productfsk extends \Magento\Framework\View\Element\Template
{
	protected $_coreRegistry = null;
	protected $_objectManager;
	/*
	*
	*class cunstructor 
	*
	*/
	function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\ObjectManagerInterface $objectmanager
		){
		$this->_objectManager = $objectmanager;
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context);
	}
    /*
	*
	*display category id
	*
	*/
	public function getCategoryId(){
		$test = $this->_coreRegistry->registry('current_product');

		$productId = $test->getId();
		$productData = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);		
		return $productData->getFskProductType();//$test->getId();
	}
	/*
	*
	*display category name
	*
	*/
    public function getFskUserIp()
    {
    	$geoIp = $this->_objectManager->create('Mangoit\Vendorcommission\Plugin\Action');
    	$currentIp = $geoIp->getUserIp();
    	return $currentIp;
    }

	function getCategoryName(){
		$test = $this->_coreRegistry->registry('current_category');
		/*echo "".$test->getName();*/
		return $test->getName();
	}
	/*
	*
	*display product id
	*
	*/
	function getProductId(){
		$test = $this->_coreRegistry->registry('current_product');
		/*echo "".$test->getId();*/
		return $test->getId();
	}
	/*
	*
	*display product name
	*
	*/
	function getProductName(){
		$test = $this->_coreRegistry->registry('current_product');
		/*echo "".$test->getName();*/
		return $test->getName();
	}	
}