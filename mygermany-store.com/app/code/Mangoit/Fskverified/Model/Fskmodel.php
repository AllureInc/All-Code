<?php
namespace  Mangoit\Fskverified\Model;
class Fskmodel extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'fsk_agreed_user';

	protected $_cacheTag = 'fsk_agreed_user';

	protected $_eventPrefix = 'fsk_agreed_user';

	protected function _construct()
	{
		$this->_init('Mangoit\Fskverified\Model\ResourceModel\Fskmodel');
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