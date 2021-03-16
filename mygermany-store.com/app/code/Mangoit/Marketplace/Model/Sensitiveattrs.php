<?php
namespace  Mangoit\Marketplace\Model;

class Sensitiveattrs extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('Mangoit\Marketplace\Model\ResourceModel\Sensitiveattrs');
	}
}