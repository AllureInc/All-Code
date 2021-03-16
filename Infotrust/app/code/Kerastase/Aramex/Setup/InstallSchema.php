<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/15/19
 * Time: 13:44 PM
 */
namespace Kerastase\Aramex\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
           $table = $setup->getConnection()->newTable(
            $setup->getTable('stock_reconciliation_history')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'History ID'
            )->addColumn(
                'sku',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product SKU'
            )->addColumn(
                'old_qty',
                Table::TYPE_FLOAT,
                null,
                ['default' => 0],
                'Old Quantity'
            )->addColumn(
                'new_qty',
                Table::TYPE_FLOAT,
                null,
                ['default' => 0],
                'New Product Qty'
            )->addColumn(
                   'comment',
                   Table::TYPE_TEXT,
                   null,
                   ['nullable' => false],
                   'Comment'
               )
               ->addColumn(
                'updated_at',
                Table::TYPE_DATE,
                null,
               ['nullable' => false],
                'Modified At'
            );
            $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}