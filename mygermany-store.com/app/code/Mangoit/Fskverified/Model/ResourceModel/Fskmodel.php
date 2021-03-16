<?php
namespace Mangoit\Fskverified\Model\ResourceModel;


class Fskmodel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('fsk_agreed_user', 'id');
	}
	
}