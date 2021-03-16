<?php
namespace  Mangoit\Fskverified\Model;
class Comment extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'order_comments';

	protected $_cacheTag = 'order_comments';

	protected $_eventPrefix = 'order_comments';

	protected function _construct()
	{
		$this->_init('Mangoit\Fskverified\Model\ResourceModel\Comment');
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