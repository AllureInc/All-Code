<?php
namespace Mangoit\VendorPayments\Model;

class Ordercancelemail extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'mits_cancel_order_request';

	protected $_cacheTag = 'mits_cancel_order_request';

	protected $_eventPrefix = 'mits_cancel_order_request';

	protected function _construct()
	{
		$this->_init('Mangoit\VendorPayments\Model\ResourceModel\Ordercancelemail');
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