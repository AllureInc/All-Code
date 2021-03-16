<?php
/**
 * Module: Cor_MerchandiseHandling
 * Setup Install Script.
 */
namespace Cor\MerchandiseHandling\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('cor_merchandise')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('cor_merchandise')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Id'
            )
            ->addColumn(
                'product_parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product Parent Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product SKU'
            )
            ->addColumn(
                'purchase_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0,'nullable' => false],
                'Purchase Order Number'
            )
            ->addColumn(
                'event_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0,'nullable' => false],
                'Event Id'
            )
            ->addColumn(
                'artist_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Artist Id'
            )
            ->addColumn(
                'count_in',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Count In'
            )
            ->addColumn(
                'add_on',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Add On'
            )
            ->addColumn(
                'total',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Count In + Add On'
            )
            ->addColumn(
                'comp',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Complimentry'
            )
            ->addColumn(
                'count_out',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Count out'
            )
            ->addColumn(
                'total_sold',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Total sold'
            )
            ->addColumn(
                'gross_sale',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Gross Sale'
            )
            ->setComment('Merchandise Table');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
