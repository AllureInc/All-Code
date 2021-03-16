<?php 
namespace Mangoit\Orderdispatch\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup2
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup2 = $setup2;
    }


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
    
             $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'vendor_tracking_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'Vendor Tracking Id'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_grid'),
                'vendor_tracking_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'Vendor Tracking Id'
                )
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
    
            $status = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order\Status');

            $status->setData('status', 'order_verified')->setData('label', 'Order Verified')->unsetData('id')->save();
            $status->assignState('complete', true, true);

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'vendor_to_mygermany_cost',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'Vendor to myGermany cost'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'scc_cost',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'SCC cost'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('marketplace_saleslist'),
                'vendor_to_mygermany_cost',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'Vendor to myGermany cost'
                )
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('ced_productfaq'),
                'store_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'default'  => 0,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'   => 'Store ID'
                )
            );
            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $orderTable = 'sales_order';
            //Order table
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable($orderTable),
                    'liferay_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 10,
                        'default'  => 0,
                        'unsigned' => true,
                        'nullable' => false,
                        'comment' => 'Liferay Order Id'
                    ]
                );
            $setup->endSetup(); 
        }
    }
}