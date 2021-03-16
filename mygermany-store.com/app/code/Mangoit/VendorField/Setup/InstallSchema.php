<?php

namespace Mangoit\VendorField\Setup;

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

        // first table

        $vendorRestrictedTbl = 'vendor_custom_fields';
        $table = $installer->getConnection()->newTable($installer->getTable($vendorRestrictedTbl))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'Primary Key'
                            )

                        ->addColumn(
                                'product_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'Product Id'
                        )

                        ->addColumn(
                                'product_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'Product name'
                            )

                        ->addColumn(
                                'custom_fields',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Custom fields'
                            )

                        ->addColumn(
                                'custom_field_value',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Custom fields'
                            )

                        ->addColumn(
                                'created_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['nullable' => false],
                                'Created At'
                            )

                        ->addColumn(
                                'updated_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                 null,
                                ['nullable' => false],
                                'Updated At'
                            );        
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
    }
}