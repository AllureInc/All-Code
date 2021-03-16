<?php

namespace Mangoit\VendorAttribute\Setup;

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
        $vendorAttributes = 'vendor_attributes';
            $table = $installer->getConnection()->newTable($installer->getTable($vendorAttributes))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'Primary Key'
                            )

                         ->addColumn(
                                'vendor_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'vendor id'
                            )

                          ->addColumn(
                                'attribute_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'attribute id'
                            )

                          ->addColumn(
                                'attribute_code',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'attribute code'
                            )

                          ->addColumn(
                                'is_global',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                                'is global'
                            );
           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
    }
}