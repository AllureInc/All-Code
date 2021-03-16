<?php
namespace Mangoit\VendorAttribute\Model\ResourceModel;


class Attributemodel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('vendor_attributes', 'id');
	}
	
}