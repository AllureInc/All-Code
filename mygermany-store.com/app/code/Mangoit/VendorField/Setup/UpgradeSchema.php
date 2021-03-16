<?php 
namespace Mangoit\VendorField\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $vendorCustomFields = 'vendor_custom_fields';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($vendorCustomFields),
                'vendor_comments',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '',
                    'nullable' => true,
                    'comment' => 'Vendor Comments'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($vendorCustomFields),
                'vendor_input',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Vendor selected values'
                ]
            );
            $installer->getConnection()->changeColumn(
                $installer->getTable($vendorCustomFields),
                'created_at',
                'created_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment' => 'Creation Time'
                ]
            );
            $installer->getConnection()->changeColumn(
                $installer->getTable($vendorCustomFields),
                'updated_at',
                'updated_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Update Time'
                ]
            );

            $installer->endSetup();
        }

    }
}