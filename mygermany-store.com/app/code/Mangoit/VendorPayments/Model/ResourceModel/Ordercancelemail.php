<?php
namespace Mangoit\VendorPayments\Model\ResourceModel;


class Ordercancelemail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('mits_cancel_order_request', 'id');
	}
	
}