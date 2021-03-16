<?php
namespace Mangoit\Marketplace\Model\ResourceModel;


class Sensitiveattrs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('mangoit_sensitive_attributes', 'id');
	}
	
}