<?php 
namespace Mangoit\Productfaq\Setup;
 
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
            $ced_productfaq = 'ced_productfaq';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($ced_productfaq),
                'admin_notification',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Notification flag for admin'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $ced_productfaq = 'ced_productfaq';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($ced_productfaq),
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'Updated At'
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $table = $installer->getConnection()->newTable(
            $installer->getTable('mis_productfaq')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('identity' => true,'unsigned' => true,'nullable'  => false,'primary'   => true,),
                'id'
            )
            ->addColumn(
                'default_faq_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, array(
                ), 'default store FAQ ID'
            )
            ->addColumn(
                'storewise_faq_ids', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2055, array(
                ), 'Storewise FAQ Ids'
            );
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            // creating customer attributes
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerSetup = $objectManager->create('Mangoit\Productfaq\Setup\CustomerSetup');
                $customerSetup->installCustomerAttributes();
            // creating customer attributes
            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $ced_productfaq = 'ced_productfaq';
             $installer->getConnection()->addColumn(
                $installer->getTable($ced_productfaq),
                'parent_faq_id',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'unsigned' => true,
                    'nullable' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'Parent Faq Id'
                ]
            );
             $installer->getConnection()->addColumn(
                $installer->getTable($ced_productfaq),
                'is_translated',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'unsigned' => true,
                    'nullable' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'is_translated'
                ]
            );
            $installer->endSetup();
        }
    }
}