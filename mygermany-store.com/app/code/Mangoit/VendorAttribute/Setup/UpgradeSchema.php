<?php 
namespace Mangoit\VendorAttribute\Setup;
 
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
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup(); 

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $vendor_attributes = 'vendor_attributes';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($vendor_attributes),
                'attribute_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'attribute type'
                ]
            );
            $installer->getConnection()->addColumn(
                    $installer->getTable($vendor_attributes),
                    'created_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Created on'
                    ]
                );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $vendor_attributes = 'vendor_attributes';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($vendor_attributes),
                'is_visible',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '1',
                    'after' => 'attribute_type',
                    'comment' => 'Visible Attribute'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($vendor_attributes),
                'associated_attribute',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'after' => 'is_visible',
                    'comment' => 'associative attributes'
                ]
            );

            $installer->getConnection()->addColumn(
                    $installer->getTable($vendor_attributes),
                    'parant_attribute_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'after' => 'associated_attribute',
                        'comment' => 'Parant Attribute Id'
                    ]
                );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $vendor_attributes = 'vendor_attributes';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($vendor_attributes),
                'attribute_label',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'after' => 'attribute_code',
                    'comment' => 'Attribute Label'
                ]
            );

            $installer->endSetup();
        }

      
    }
}

