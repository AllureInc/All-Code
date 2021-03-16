<?php

namespace Mangoit\FskRestricted\Setup;

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

        $vendorRestrictedTbl = 'vendor_restricted_products';
        $table = $installer->getConnection()->newTable($installer->getTable($vendorRestrictedTbl))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'Primary Key'
                            )

                        ->addColumn(
                                'category_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Category Id'
                        )

                        ->addColumn(
                                'category_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Category name'
                            )

                         ->addColumn(
                                'product_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Product Id'
                            )

                          ->addColumn(
                                'product_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Product name'
                            )

                          ->addColumn(
                                'product_status',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Approved by admin or not'
                            )
                        ->addColumn(
                                'vendor_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Vendor name of this product'
                            )
                        ->addColumn(
                                'restricted_countries',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Countries in wich this product is restricted'
                            );           
            $installer->getConnection()->createTable($table);
        // Second Table 
         
        $vendorRestrictedCatTbl = 'vendor_restricted_categories';
        $tableTwo = $installer->getConnection()->newTable($installer->getTable($vendorRestrictedCatTbl))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'Primary Key'
                            )

                         ->addColumn(
                                'category_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Category Id'
                            )

                          ->addColumn(
                                'category_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Category name'
                            )

                        ->addColumn(
                                'restricted_countries',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => true],
                                'Countries in wich this categories is restricted'
                            );           
            $installer->getConnection()->createTable($tableTwo);


            $installer->endSetup();
    }
}