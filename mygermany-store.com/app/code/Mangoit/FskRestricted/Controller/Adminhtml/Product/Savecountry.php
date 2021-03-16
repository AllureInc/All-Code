<?php

namespace Mangoit\FskRestricted\Controller\Adminhtml\Product;

use Magento\Customer\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

/**
* 
*/
class Savecountry extends \Magento\Backend\App\Action
{
	protected $_date;
	protected $_formKeyValidator;
	protected $_customerSession;
	protected $_objectManager;

	public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
	{
		$this->_objectManager = $objectmanager;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);		
	}

	public function execute()
	{
		$wholedata = $this->getRequest()->getParams();
		$resultArray = [];
		unset($wholedata['key']);
		unset($wholedata['form_key']);
		$procatIds = unserialize($wholedata['categoryIds']);
		$storeValue = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $countryModel = $this->_objectManager->create('Magento\Directory\Model\Country');
        
        // $countries = $storeValue->getValue('general/country/allow');
        // $countryArray = explode(',', $countries);

        foreach ($wholedata['countries'] as $value) {
            # code...
            $countryName = $countryModel->loadByCode($value)->getName();
            array_push($resultArray, $countryName);
        }
		$countryName =  implode(',', $resultArray);
		$itemIds = explode(",", $wholedata['productIds']);
		foreach ($itemIds as $value) {
			$model = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct')->getCollection();
			foreach ($procatIds as $newkey => $newvalue) {
				$model->clear()->getSelect()->reset('where');
			    $model->addFieldToFilter('category_id', array('eq' => $newvalue['category_id']))->addFieldToFilter('product_id', array('eq' => $newvalue['product_id']));
		        foreach ($model as $item) {
		        	$productModel = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');        	
		        	$productModel->load($item->getId());
		        	$productModel->setRestrictedCountries($countryName);
		        	$productModel->save();
		        	$productModel->unsetData();	
		        }
			}
			
			
		}
		$this->messageManager->addSuccess( __('Product restricted successfully. Please reindex Solr in order to apply your changes.'));
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
	}
}