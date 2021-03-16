<?php

namespace Mangoit\Vendorcommission\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
* 
*/
class InstallData implements InstallDataInterface
{
	protected $_postFactory;
	public function __construct(\Mangoit\Vendorcommission\Model\TurnoverFactory $turnoverFactory)
	{
		$this->_postFactory = $turnoverFactory;
	}

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$data = [
			'commission_rule'       => 'a:2:{s:11:"Electronics";a:2:{s:6:"0-1000";s:2:"10";s:10:"1001-above";s:1:"5";}s:15:"Non-Electronics";a:2:{s:6:"0-1000";s:2:"15";s:10:"1001-above";s:2:"10";}}'
		];
		$post = $this->_postFactory->create();
		$post->addData($data)->save();
	}
}