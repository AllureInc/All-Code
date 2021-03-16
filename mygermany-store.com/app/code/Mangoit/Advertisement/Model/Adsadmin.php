<?php
namespace Mangoit\Advertisement\Model;
class Adsadmin extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'mis_admin_ads';

	protected $_cacheTag = 'mis_admin_ads';

	protected $_eventPrefix = 'mis_admin_ads';

	protected function _construct()
	{
		$this->_init('Mangoit\Advertisement\Model\ResourceModel\Adsadmin');
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