<?php

namespace Mangoit\Vendorcommission\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
* 
*/
class InstallSchema implements InstallSchemaInterface
{
	
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		 $table = $installer->getConnection()
		 ->addColumn(
                $installer->getTable('marketplace_saleperpartner'),
                'seller_turnover',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' =>'12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment' => 'seller turnover'
                ]
        );

        $installer->endSetup();
	}
}