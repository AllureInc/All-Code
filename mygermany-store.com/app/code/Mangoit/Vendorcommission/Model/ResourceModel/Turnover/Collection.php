<?php
namespace Mangoit\Vendorcommission\Model\ResourceModel\Turnover;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'turnover_id';
	protected $_eventPrefix = 'vendor_turnover';
	protected $_eventObject = 'vendor_turnover';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\Vendorcommission\Model\Turnover', 'Mangoit\Vendorcommission\Model\ResourceModel\Turnover');
	}

}