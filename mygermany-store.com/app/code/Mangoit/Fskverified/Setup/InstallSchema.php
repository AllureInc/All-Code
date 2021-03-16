<?php

namespace Mangoit\Fskverified\Setup;

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
        $fskAgreedUser = 'fsk_agreed_user';
            $table = $installer->getConnection()->newTable($installer->getTable($fskAgreedUser))
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
                                'Product ID'
                            )

                          ->addColumn(
                                'user_ip',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'User Ip'
                            )

                          ->addColumn(
                                'fsk',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                                'FSK agreed'
                            )

                         ->addColumn(
                                'created_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                'Created At'
                            )
                         ->addColumn(
                                'updated_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                 null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                                'Updated At'
                            );
           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
    }
}