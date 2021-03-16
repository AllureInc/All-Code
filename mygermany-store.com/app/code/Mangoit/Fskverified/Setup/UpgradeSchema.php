<?php 
namespace Mangoit\Fskverified\Setup;
 
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

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $order_comments = 'order_comments';
            $table = $installer->getConnection()->newTable($installer->getTable($order_comments))
                        ->addColumn(
                            'id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'id'
                            )

                         ->addColumn(
                                'order_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false],
                                'Order ID'
                            )

                         ->addColumn(
                                'seller_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                '10',
                                ['nullable' => false],
                                'Seller Id'
                            )

                         ->addColumn(
                                'seller_name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                '64',
                                ['nullable' => false, 'default' => ''],
                                'Seller Name'
                            )

                         ->addColumn(
                                'comment',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['nullable' => false, 'default' => ''],
                                'Comment'
                            )

                         ->addColumn(
                                'created_on',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                null,
                                ['nullable' => false, 
                                'default' => ''],
                                'Created On'
                            );

           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

      
    }
}

