<?php
namespace Mangoit\Productfaq\Model\ResourceModel\Misproductfaq;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mangoit\Productfaq\Model\Misproductfaq', 'Mangoit\Productfaq\Model\ResourceModel\Misproductfaq');
	}

}