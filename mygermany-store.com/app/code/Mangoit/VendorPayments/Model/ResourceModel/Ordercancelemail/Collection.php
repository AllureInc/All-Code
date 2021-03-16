<?php
namespace Mangoit\VendorPayments\Model\ResourceModel\Ordercancelemail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'mits_cancel_order_request';
	protected $_eventObject = 'mits_cancel_order_request';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\VendorPayments\Model\Ordercancelemail', 'Mangoit\VendorPayments\Model\ResourceModel\Ordercancelemail');
	}

}