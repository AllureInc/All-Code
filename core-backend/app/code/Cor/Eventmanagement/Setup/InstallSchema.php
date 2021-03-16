<?php
namespace Cor\Eventmanagement\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'cor_events'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('cor_events')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )
        ->addColumn(
            'event_start_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'event_start_date'
        )
        ->addColumn(
            'event_end_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'event_end_date'
        )
        ->addColumn(
            'event_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'event_name'
        )
        ->addColumn(
            'event_street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'event_street'
        )
        ->addColumn(
            'event_city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'event_city'
        )
        ->addColumn(
            'event_state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'event_state'
        )
        ->addColumn(
            'event_zip',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'event_zip'
        )
        ->addColumn(
            'event_country',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'event_country'
        )
        ->addColumn(
            'event_capacity',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'event_capacity'
        )
        ->addColumn(
            'event_phone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'event_phone'
        )
        ->addColumn(
            'tax_values',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'tax_values'
        )
        ->addColumn(
            'event_time_zone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Time Zone'
        )   
        ->setComment(
            'Cor Eventmanagement'
        );
        
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
