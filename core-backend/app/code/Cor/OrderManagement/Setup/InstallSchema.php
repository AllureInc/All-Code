<?php
 
namespace Cor\OrderManagement\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('quote_item'),
            'cor_location',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Location field'
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order_item'),
            'cor_location',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Location field'
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order_item'),
            'cor_item_pick_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '0',
                'comment' => 'Order item pick status'
            ]
        );

        $installer->endSetup();
    }
}
