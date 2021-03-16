<?php


namespace Kerastase\Aramex\Setup;



    use Magento\Framework\DB\Ddl\Table;
    use Magento\Framework\Setup\UpgradeSchemaInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'shipment_awb',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'AWB'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            $table = $connection->newTable(
                $setup->getTable('order_changes_logs')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Log ID'
            )->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order Increment Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Order status'
            )->addColumn(
                'action',
                Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Action'
            )->addColumn(
                'cron_runing',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Running Cron'
            )->addColumn(
                'comments',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Comments'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'Created At'
            );
            $connection->createTable($table);

        }




        $installer->endSetup();
    }
}
