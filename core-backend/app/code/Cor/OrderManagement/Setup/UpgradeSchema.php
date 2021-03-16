<?php 
namespace Cor\OrderManagement\Setup; 
 
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

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $salesoderitemTable = 'sales_order_item';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($salesoderitemTable),
                'artist',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => false,
                    'default'  => '',
                    'length'    => 255,
                    'unsigned' => true,
                    'comment'   => 'Artist'
                )
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($salesoderitemTable),
                'event',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => 255,
                    'default'    => '',
                    'comment'   => 'Event'
                )
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($salesoderitemTable),
                'category',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => 255,
                    'default'    => '',
                    'comment'   => 'Category'
                )
            );

            $quoteitemTable = 'quote_item';
            $installer->getConnection()->addColumn(
                $installer->getTable($quoteitemTable),
                'artist',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => false,
                    'default'  => '',
                    'length'    => 255,
                    'unsigned' => true,
                    'comment'   => 'Artist'
                )
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($quoteitemTable),
                'event',
                 array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => 255,
                    'default'    => '',
                    'comment'   => 'Event'
                )
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $salesoderitemTable = 'sales_order_item';

            $installer->getConnection()->changeColumn(
                $installer->getTable($salesoderitemTable),
                'artist',
                'cor_artist_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment' => 'Artist id associated from the product selected.',
                ]
            );

            $installer->getConnection()->changeColumn(
                $installer->getTable($salesoderitemTable),
                'event',
                'cor_event_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment' => 'Event id associated from the product selected.',
                ]
            );

            $installer->getConnection()->changeColumn(
                $installer->getTable($salesoderitemTable),
                'category',
                'cor_artist_category_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment' => 'Artist category id associated from the product selected.',
                ]
            );

            $quoteitemTable = 'quote_item';

            $installer->getConnection()->changeColumn(
                $installer->getTable($quoteitemTable),
                'artist',
                'cor_artist_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment' => 'Artist id associated from the product selected.',
                ]
            );

            $installer->getConnection()->changeColumn(
                $installer->getTable($quoteitemTable),
                'event',
                'cor_event_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => 0,
                    'comment' => 'Event id associated from the product selected.',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($quoteitemTable),
                'cor_artist_category_id',
                 array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => false,
                    'default'    => 0,
                    'comment' => 'Artist category id associated from the product selected.',
                )
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $salesOrderTable = 'sales_order';

            $installer->getConnection()->addColumn(
                $installer->getTable($salesOrderTable),
                'cor_one_time_key',
                 array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => false,
                    'length'    => 11,
                    'comment' => 'Cor randomly generated 6 digit numeric order one time key.',
                )
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $salesOrderTable = 'sales_order';

            $installer->getConnection()->addColumn(
                $installer->getTable($salesOrderTable),
                'cor_customer_signature',
                 array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                    'nullable'  => false,
                    'comment' => 'Cor customer signature.',
                )
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $salesOrderTable = 'sales_order';

            $installer->getConnection()->dropColumn($installer->getTable($salesOrderTable), 'cor_customer_signature');

            $installer->getConnection()->addColumn(
                $installer->getTable($salesOrderTable),
                'cor_include_sig',
                 array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => false,
                    'length'    => 11,
                    'default'  => 0,
                    'comment' => 'Cor include customer signature (boolean status 0/1).',
                )
            );

            $table = $installer->getConnection()->newTable(
                $installer->getTable('cor_order_customer_signature')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'magento_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Magento Order Id'
            )
            ->addColumn(
                'customer_signature',
                \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                null,
                ['nullable' => false],
                'Customer Signature'
            )
            ->setComment(
                'Cor Customer Order Signature Management'
            );        
            $installer->getConnection()->createTable($table);

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $salesOrderTable = 'sales_order';

            $installer->getConnection()->dropColumn($installer->getTable($salesOrderTable), 'cor_include_sig');

            $installer->endSetup();
        }
    }
}

