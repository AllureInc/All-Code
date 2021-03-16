<?php

namespace Mangoit\Vendorcommission\Helper;

/**
* 
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $orderId;
    protected $countryCode;
    protected $eavConfig;
    protected $_objectManager;
    protected $_pluginAction;
    protected $_marketplaceHelper;
    protected $_connection;

	function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        \Magento\Eav\Model\Config $eavConfig,
        \Mangoit\Vendorcommission\Plugin\Action $pluginAction,
        \Mangoit\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\ResourceConnection $resource
    )
	{
		parent::__construct($context);
        $this->_eavConfig = $eavConfig;
        $this->_objectManager = $objectmanager;
        $this->_pluginAction = $pluginAction;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_connection = $resource->getConnection();
	}

    /*
    * Function for tax label on product 
    * returns booleans true|false
    *
    */
    public function getFinalPriceTaxLabel()
    {
        // $pluginAction = $this->_objectManager->create('Mangoit\Vendorcommission\Plugin\Action');
        // $misHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\Data');
        $country = $this->_pluginAction->getCountry();
        $euCountry = $this->_marketplaceHelper->isEuCountry($country);
        return $euCountry;
    }

    public function getTaxableProductPrice($product, $price)
    {
        $product_tax_class_id = $product->getTaxClassId();
        $tax_rate = 0; 
        try {
            $myTable = $this->_connection->getTableName('table_name');
            $sql     = $this->_connection->select()->from(
                ["tn" => 'tax_calculation'],
                ['tax_calculation_rate_id']
            )->where('product_tax_class_id = ?', $product_tax_class_id); 

            $tax_calculation_rate_id  = $this->_connection->fetchOne($sql);
            if ($tax_calculation_rate_id) {
                $tax_rate =  $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate')->load($tax_calculation_rate_id, 'tax_calculation_rate_id')->getRate();
            }

            if ($tax_rate > 0) {
                $product_price = ((float) $price * (float) $tax_rate)/100;
                return $product_price;
                # code...
            } else {
                return $price;
            }
            
        } catch (Exception $e) {
            return $price;            
        }
    }

    public function getCommissionRules($sellerId)
    {
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $newObjectManager->create(
            'Webkul\Marketplace\Model\Saleperpartner'
            )->getCollection()->addFieldToFilter('seller_id',['eq' => $sellerId]);
        return $collection;
    }

    public function getProductNoteAttribute()
    {
        $attributeObject = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $attrCollection  = $attributeObject->getCollection()->addFieldToFilter('attribute_code',['eq' => 'product_note']);
        return $attrCollection;
    }

    public function getCustomAttributeOption()
    {
        $attributeCode = "product_cat_type";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

	public function getCountryCode($id)
	{
		$this->orderId = $id;
	}

	public function getOrderData()
	{
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollection = $newObjectManager->create('Magento\Sales\Model\Order')->load($this->orderId);
        $tax = '';
        if (!empty($orderCollection->debug())) {
            $orderCollectionObj = $orderCollection->getShippingAddress();
            if (!empty($orderCollectionObj)) {
                $data = $orderCollectionObj->getData();
                $countryId = $data['country_id'];
                $storeValue = $newObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
                $eu_countries = $storeValue->getValue('general/country/eu_countries');
                $eu_array = explode(',', $eu_countries);
                if (in_array($countryId, $eu_array)) {
                    $tax = " (Incl. tax)";
                } else {
                    $tax = '';
                }
            } 
        }
        return $tax;
	}
    
    public function getCountryByCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function isEuropian()
    {
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeValue = $newObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $eu_countries = $storeValue->getValue('general/country/eu_countries');
        $eu_array = explode(',', $eu_countries);
        if (in_array($this->countryCode, $eu_array)) {
            $res = "1";
        } else {
            $res = "0";
        }

        return $res;
    }
    public function getAttributeArray()
    {
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionArray = $newObjectManager->create(
            'Mangoit\Vendorcommission\Helper\Data'
            )->getCustomAttributeOption();
        return $collectionArray;
    }

    public function getMyProductAttribute($id)
    {
        $resultData ="";
        $productCollection = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        // print_r($productCollection->getCustomAttribute('product_note')->getValue());
        if($productCollection->getCustomAttribute('product_note')){
            $resultData =  $productCollection->getCustomAttribute('product_note')->getValue();
        } else {
            $resultData = '0';
        }
        return $resultData;
    }

    public function getCustomerIdFromSession()
    {
        $sessionModel = $this->_objectManager->create('Magento\Customer\Model\Session');
        $customerId = $sessionModel->getCustomer()->getId();
        return $customerId;
    }

    public function getSellerComment()
    {
        $commentCollection = $this->_objectManager->create('Mangoit\Fskverified\Model\Comment');
        return $commentCollection;
    }
}