<?php
namespace Mangoit\VendorField\Model\ResourceModel\Fieldmodel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'vendor_custom_fields';
	protected $_eventObject = 'vendor_custom_fields';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\VendorField\Model\Fieldmodel', 'Mangoit\VendorField\Model\ResourceModel\Fieldmodel');
	}

}