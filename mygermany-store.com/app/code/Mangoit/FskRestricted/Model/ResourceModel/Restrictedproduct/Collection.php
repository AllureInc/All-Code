<?php
namespace Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'vendor_restricted_products';
	protected $_eventObject = 'vendor_restricted_products';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\FskRestricted\Model\Restrictedproduct', 'Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct');
	}

}