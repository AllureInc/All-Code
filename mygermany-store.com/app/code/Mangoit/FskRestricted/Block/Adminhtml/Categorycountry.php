<?php

namespace Mangoit\FskRestricted\Block\Adminhtml; 
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

use Magento\Ui\Component\MassAction\Filter;
/**
* allow
*/
class Categorycountry extends \Magento\Framework\View\Element\Template
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

    public function getSaveUrl()
    {
    //replace the tab with the url you want
        return $this->getUrl('fskrestricted/category/makecategoryrestricted');
    }

    public function getCategoriesList()
    {
        $categoryModel =  $this->newObjectManager->create('Magento\Catalog\Model\Category');
       
        return $categoryModel->getCollection()->addAttributeToSelect('*');
    }

    public function getParameters()
    {
        $controller  =  $this->newObjectManager->create('Mangoit\FskRestricted\Controller\Adminhtml\Category\Categoryform');
        return $controller->getAllParameters();

    }

    public function getNameOfCategories()
    {
        $categoryNames = [];
        $categoryIds = $this->getParameters();
        $categoryModelData = $this->getCategoriesList();
        // print_r($categoryIds);
        // die("14152");
        foreach ($categoryIds as $value) {
            foreach ($categoryModelData as $category) {               
                if (in_array($category->getEntityId(), $value)) {
                    array_push($categoryNames, $category->getName());
                    
                }
            }
            
            
        }
        // print_r($categoryNames);
    // die("121"); 
        return $categoryNames;
    }
     
    public function getIdsOfCategories()
    {
        $categoryIdArray = [];
        $counter = 0;
        $categoryIds = $this->getParameters();
        foreach ($categoryIds as $key => $value) {
            array_push($categoryIdArray, $value);
            $counter++;
        }

        return $categoryIdArray;
    }

    public function getCountriesName()
    {
        $countryNames  =  $this->newObjectManager->create('Mangoit\FskRestricted\Block\Adminhtml\Addcountry');
        return $countryNames->getCountryList();
    }

    public function getRestrictedCountriesNames($id)
    {
        $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory')->load($id, 'category_id');
        if($categoryModel->hasData() == 1) {
            return $categoryModel->getRestrictedCountries();
        } else {
            return "no";
        }
    }

}