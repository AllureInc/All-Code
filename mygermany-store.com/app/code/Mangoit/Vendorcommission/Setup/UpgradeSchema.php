<?php 
namespace Mangoit\Vendorcommission\Setup;
 
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
            $vendor_turnover = 'vendor_turnover';
            $table = $installer->getConnection()->newTable($installer->getTable($vendor_turnover))
                        ->addColumn(
                            'turnover_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'turnover id'
                            )

                         ->addColumn(
                                'seller_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                                'Seller ID'
                            )

                         ->addColumn(
                                'seller_turnover',
                                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                                '12,4',
                                ['nullable' => false, 'default' => '0.0000'],
                                'Total turnover'
                            );
           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $vendor_turnover = 'vendor_turnover';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($vendor_turnover),
                'vender_commission',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Vendor commission'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {

            // $installer->getConnection()->changeColumn(
            //     $installer->getTable('marketplace_saleperpartner'),
            //     'seller_turnover',
            //     'commission_rule',
            //     [
            //         'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            //         'nullable' => true,
            //         'comment' => 'commission rule per vendor'
            //     ]
            // );
            // $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $vendor_turnover = 'vendor_turnover';
            // $connection->dropColumn($vendor_turnover, 'del_flg');
    
            // $installer->getConnection()->dropColumn($installer->getTable('vendor_turnover'), 'seller_id')->dropColumn($installer->getTable('vendor_turnover'), 'seller_turnover');

            if ($installer->getConnection()->isTableExists($vendor_turnover) == true) {
                $connection = $installer->getConnection();

                // del_flg = column name which you want to delete
                $connection->dropColumn($vendor_turnover, 'seller_id');
            }

            // $installer->getConnection()->changeColumn(
            //     $installer->getTable('vendor_turnover'),
            //     'vendor_commission',
            //     'commission_rule',
            //     [
            //         'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            //         'nullable' => true,
            //         'comment' => 'commission rule per vendor'
            //     ]
            // );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $vendor_turnover = 'vendor_turnover';
            if ($installer->getConnection()->isTableExists($vendor_turnover) == true) {
                $connection = $installer->getConnection();
                $connection->dropColumn($vendor_turnover, 'seller_turnover');
            }

            // $installer->getConnection()->changeColumn(
            //     $installer->getTable('vendor_turnover'),
            //     'vender_commission',
            //     'commission_rule',
            //     [
            //         'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            //         'nullable' => true,
            //         'comment' => 'global commission rule '
            //     ]
            // );
            $installer->endSetup();
        }

    }
}

