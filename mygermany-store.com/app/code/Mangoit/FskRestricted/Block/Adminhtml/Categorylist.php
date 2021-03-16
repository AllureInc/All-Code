<?php

namespace Mangoit\FskRestricted\Block\Adminhtml; 
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

use Magento\Ui\Component\MassAction\Filter;
/**
* allow
*/
class Categorylist extends \Magento\Framework\View\Element\Template
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
        return $this->getUrl('fskrestricted/category/categoryform');
    }

    public function getCategoriesList()
    {
        $categoryModel =  $this->newObjectManager->create('Magento\Catalog\Model\Category');

        return $categoryModel->getCollection();
    }

    public function getRestrictedCountries($id)
    {
        $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory')->load($id, 'category_id');
        return $categoryModel->getRestrictedCountries();

    }
}