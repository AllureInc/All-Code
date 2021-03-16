<?php
namespace Mangoit\FskRestricted\Model;
class Restrictedcategory extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'vendor_restricted_categories';

	protected $_cacheTag = 'vendor_restricted_categories';

	protected $_eventPrefix = 'vendor_restricted_categories';

	protected function _construct()
	{
		$this->_init('Mangoit\FskRestricted\Model\ResourceModel\Restrictedcategory');
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