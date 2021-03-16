<?php 
namespace Cor\Eventmanagement\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void order_comments
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table_name = 'cor_event_artist';
            $table = $installer->getConnection()->newTable($installer->getTable($table_name))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'id'
                            )
                         ->addColumn(
                                'event_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'Event Id'
                            )
                         ->addColumn(
                                'artist_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['nullable' => false],
                                'Artist Id'
                            )
                         ->addColumn(
                                'artist_cut',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['nullable' => false, 'default' => ''],
                                'Artist Cut'
                            )
                        ->addColumn(
                                'created_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                'Creation Time'
                            )
                            ->addColumn(
                                'updated_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                                'Update Time'
                            );           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table_name = 'cor_events';
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'event_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'Event Status',
                    'after' => 'tax_values',
                    'default' => 0,
                ]
            );
            $installer->endSetup();
        }
      
    }
}

