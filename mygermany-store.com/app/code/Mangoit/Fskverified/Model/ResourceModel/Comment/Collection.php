<?php
namespace Mangoit\Fskverified\Model\ResourceModel\Comment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'order_comments';
	protected $_eventObject = 'order_comments';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\Fskverified\Model\Comment', 'Mangoit\Fskverified\Model\ResourceModel\Comment');
	}

}