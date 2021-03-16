<?php 

namespace Mangoit\VendorField\Helper;

/**
* 
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_objectManager;
	
	public function __construct(\Magento\Framework\App\Helper\Context $context, 
		\Magento\Framework\ObjectManagerInterface $objectmanager)
	{
		parent::__construct($context);
        $this->_objectManager = $objectmanager;		
	}

	public function getModel($productId)
	{
		$model = $this->_objectManager->create('\Mangoit\VendorField\Model\Fieldmodel');
		$data = $model->getCollection()->addFieldToFilter('product_id', array('eq'=> $productId));
		return $data;
	}
}