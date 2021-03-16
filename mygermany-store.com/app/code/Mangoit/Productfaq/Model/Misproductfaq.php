<?php
namespace  Mangoit\Productfaq\Model;

class Misproductfaq extends \Magento\Framework\Model\AbstractModel
{

	protected function _construct()
	{
		$this->_init('Mangoit\Productfaq\Model\ResourceModel\Misproductfaq');
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}