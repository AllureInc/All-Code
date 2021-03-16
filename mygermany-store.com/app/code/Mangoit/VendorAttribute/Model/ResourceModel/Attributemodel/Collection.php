<?php
namespace Mangoit\VendorAttribute\Model\ResourceModel\Attributemodel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'vendor_attributes';
	protected $_eventObject = 'vendor_attributes';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\VendorAttribute\Model\Attributemodel', 'Mangoit\VendorAttribute\Model\ResourceModel\Attributemodel');
	}

}