<?php
namespace Mangoit\Fskverified\Model\ResourceModel\Fskmodel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'fsk_agreed_user';
	protected $_eventObject = 'fsk_agreed_user';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\Fskverified\Model\Fskmodel', 'Mangoit\Fskverified\Model\ResourceModel\Fskmodel');
	}

}