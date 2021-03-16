<?php
namespace  Mangoit\VendorField\Model;
class Fieldmodel extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'vendor_custom_fields';

	protected $_cacheTag = 'vendor_custom_fields';

	protected $_eventPrefix = 'vendor_custom_fields';

	protected function _construct()
	{
		$this->_init('Mangoit\VendorField\Model\ResourceModel\Fieldmodel');
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