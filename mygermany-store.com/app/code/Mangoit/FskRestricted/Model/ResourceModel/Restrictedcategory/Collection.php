<?php
namespace Mangoit\FskRestricted\Model\ResourceModel\Restrictedcategory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'vendor_restricted_categories';
	protected $_eventObject = 'vendor_restricted_categories';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\FskRestricted\Model\Restrictedcategory', 'Mangoit\FskRestricted\Model\ResourceModel\Restrictedcategory');
	}

}