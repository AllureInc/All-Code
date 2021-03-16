<?php

namespace Mangoit\FskRestricted\Helper;

use Magento\Framework\Registry;
/**
* 
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_objectmanager;
	protected $registry;
	protected $session;
	protected $_countryInterface;
	protected $_customerSession;
	protected $_customerModel;

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Session\SessionManagerInterface $sessionManagerInterface,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInterface,
        Registry $registry
        )
	{
		$this->registry = $registry;
		$this->session = $sessionManagerInterface;
		$this->_objectmanager = $objectManager;
        $this->_countryInterface = $countryInterface;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
		parent::__construct($context);
	}

	public function getCustomerModelSession()
	{
		if ($this->_customerSession->isLoggedIn()) {
			/*$customerRepository = $this->_customerSession->load($this->_customerSession->getCustomer()->getId());*/
			$customerRepository = $this->_customerModel->load($this->_customerSession->getCustomer()->getId());
			return array('is_login'=> 1, 'customerRepository'=> $customerRepository);
		} else {
			return array('is_login'=> 0);
		}
	}

    public function getCountryname($countryCode){    
		$interface = $this->_countryInterface->getCountryInfo($countryCode);
	    return $interface->getFullNameEnglish();
    }

	public function getCurrentCountry()
	{
		$methodClass = $this->_objectmanager->create('Mangoit\Vendorcommission\Plugin\Action');
		$countryClass = $this->_objectmanager->create('Mangoit\FskRestricted\Block\Adminhtml\Addcountry');
		$countryArray = $countryClass->getCountryList();
		$country = $methodClass->getCountry();
		$countryName = '';
		$countryName = $this->getCountryname($country);
		// foreach ($countryArray as $key => $value) {
		// 	if ($value['code'] == $country) {
		// 		$countryName = $value['name'];
		// 	}
		// }
		return $countryName;
	}

	public function isRestricted($productId)
	{
		// echo "<br>productId ".$productId;
		$currentCountry = $this->getCurrentCountry();
		$countryArray = [];
		$category = $this->registry->registry('current_category');
		// echo "<br> Cat :".$category->getId();
		$productModel = $this->_objectmanager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
		$productModelCollection = $productModel->getCollection();
		$productModelCollection->addFieldToFilter('category_id', array('eq' => $category->getId()))->addFieldToFilter('product_id', array('eq' => $productId));
		// print_r($productModelCollection->getData());
		if (count($productModelCollection->getData()) >= 1) {
			foreach ($productModelCollection->getData() as $item) {
				$countryArray = explode(",", $item['restricted_countries']);
			}
			if (in_array($currentCountry, $countryArray)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCurrentCategory()
	{
		$category = $this->registry->registry('current_category');
		if (sizeof($category) == 0) {
			return 'none';
		} else {
			return $category->getId();
		}
		// return $category->getId();
	}

	public function isLoggedIn()
	{
		$model = $this->_objectmanager->create('Magento\Customer\Model\Session');
		return $model->getId();
	}

	public function getSession()
	{
		return $this->session;
	}

	public function getCustomerModel()
	{
		$model = $this->_objectmanager->create('Magento\Customer\Model\Customer');
		return $model;
	}

	public function getVendorEmail($productId)
	{
		$webkulProductModel = $this->_objectmanager->create('Webkul\Marketplace\Model\Product');
		$webkulProductModel->load($productId, 'mageproduct_id');
		$vendorId = $webkulProductModel->getSellerId();
		$customerModel = $this->getCustomerModel();
		$customerModel->load($vendorId, 'entity_id');
		return $customerModel->getEmail();

	}

}