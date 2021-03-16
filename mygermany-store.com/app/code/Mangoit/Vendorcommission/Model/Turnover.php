<?php
namespace Mangoit\Vendorcommission\Model;
class Turnover extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'vendor_turnover';

	protected $_cacheTag = 'vendor_turnover';

	protected $_eventPrefix = 'vendor_turnover';

	protected function _construct()
	{
		$this->_init('Mangoit\Vendorcommission\Model\ResourceModel\Turnover');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}