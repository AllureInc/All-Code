<?php
namespace Mangoit\FskRestricted\Model;
class Restrictedproduct extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'vendor_restricted_products';

	protected $_cacheTag = 'vendor_restricted_products';

	protected $_eventPrefix = 'vendor_restricted_products';

	protected function _construct()
	{
		$this->_init('Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct');
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