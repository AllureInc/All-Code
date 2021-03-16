<?php
namespace Mangoit\Advertisement\Model\ResourceModel\Adsadmin;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'mis_admin_ads';
	protected $_eventObject = 'mis_admin_ads';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\Advertisement\Model\Adsadmin', 'Mangoit\Advertisement\Model\ResourceModel\Adsadmin');
	}

}