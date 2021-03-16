<?php
namespace  Mangoit\VendorAttribute\Model;
class Attributemodel extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'vendor_attributes';

	protected $_cacheTag = 'vendor_attributes';

	protected $_eventPrefix = 'vendor_attributes';

	protected function _construct()
	{
		$this->_init('Mangoit\VendorAttribute\Model\ResourceModel\Attributemodel');
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