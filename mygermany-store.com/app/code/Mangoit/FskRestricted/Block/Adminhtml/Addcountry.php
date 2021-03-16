<?php

namespace Mangoit\FskRestricted\Block\Adminhtml; 
use Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct\CollectionFactory;

use Magento\Ui\Component\MassAction\Filter;
/**
* allow
*/
class Addcountry extends \Magento\Framework\View\Element\Template
{
	
	protected $newObjectManager;
	protected $filter;
    protected $eavConfig;
    protected $collectionFactory;

	public function __construct(
		Filter $filter,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Eav\Model\Config $eavConfig,		
        CollectionFactory $collectionFactory)
	{
		$this->newObjectManager = $objectmanager;
		$this->filter = $filter;
        $this->_eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}
   
	public function getformKey()
    {
    	$FormKey = $this->newObjectManager->get('Magento\Framework\Data\Form\FormKey');
    	return $FormKey->getFormKey();
    }

    public function getCountryList()
    {
    	$typeArray = [];
        // $collection = $this->filter->getCollection(
        //     $this->collectionFactory->create()
        // );
        $storeValue = $this->newObjectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $countryModel = $this->newObjectManager->create('Magento\Directory\Model\Country');
        $countries = $storeValue->getValue('general/country/allow');
        $countryArray = explode(',', $countries);
        $resultArray = [];
        // echo "<pre>";
       

        // print_r($countryArray);
        // print_r(get_class_methods($countryModel));
        foreach ($countryArray as $value) {
            # code...
            $countryName = $countryModel->loadByCode($value)->getName();
            array_push($resultArray, array('code'=> $value, 'name' => $countryName));
        }
         // die("<br>4545");
        // print_r($resultArray);
        // die("<br>4545");
        return $resultArray;
    }

    public function getPostValuesFromController()
    {
        $controller = $this->newObjectManager->create('Mangoit\FskRestricted\Controller\Adminhtml\Product\Addcountry');
        $postData = $controller->getPostData();
        return $postData;

    }

    public function getSaveUrl()
    {
    //replace the tab with the url you want ->addFieldToFilter('product_id', array('eq' => $productId))
        return $this->getUrl('fskrestricted/product/savecountry');
    }

    public function getCountriesName($catId, $productId)
    {
        $productModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        $filterData = $productModel->getCollection()->addFieldToFilter('category_id', array('eq' => $catId))->addFieldToFilter('product_id', array('eq' => $productId));
        return $filterData->getData();


    }

}