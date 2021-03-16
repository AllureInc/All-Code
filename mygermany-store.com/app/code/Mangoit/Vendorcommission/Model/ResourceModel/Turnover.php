<?php
namespace Mangoit\Vendorcommission\Model\ResourceModel;


class Turnover extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('vendor_turnover', 'turnover_id');
	}
	
}