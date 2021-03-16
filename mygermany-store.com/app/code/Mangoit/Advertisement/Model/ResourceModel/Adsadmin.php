<?php
namespace Mangoit\Advertisement\Model\ResourceModel;


class Adsadmin extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('mis_admin_ads', 'id');
	}
	
}