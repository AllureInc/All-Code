<?php 
namespace Cor\MerchandiseHandling\Setup;
 
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
     * @return void sales_order_item
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $table_name = 'sales_order_item';
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'Artist category tax',
                    'default' => 0,
                ]
            );
            $installer->endSetup();
        }
      
    }
}

