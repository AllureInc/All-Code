<?php

namespace Mangoit\VendorField\Block\Adminhtml;

/**
* 
*/
class Customfield extends \Magento\Backend\Block\Template
{
	protected $_objectManager;
	protected $registry;
	protected $_template = 'Mangoit_VendorField::customfield.phtml';
	
	public function __construct(\Magento\Backend\Block\Template\Context $context, 
		\Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Framework\Registry $registry)
	{
		parent::__construct($context);
        $this->_objectManager = $objectmanager;	
        $this->registry = $registry;
	}

	protected function _construct()
    {
        $this->setTemplate($this->_template);
    }
    public function getCustomModel()
    {
    	$model = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
    	return $model;
    }

    public function getCurrentProduct()
    {
    	$product = $this->registry->registry('current_product');
    	return $product;
    }

    public function getGetAllData($productId)
    {
    	$allData = $this->_objectManager->create('Mangoit\VendorField\Helper\Data')->getModel($productId);
    	return $allData;
    }

    public function getCustomFormKey()
    {
        $form = $this->_objectManager->create('Magento\Framework\Data\Form\FormKey');
        return $form->getFormKey();
    }
}