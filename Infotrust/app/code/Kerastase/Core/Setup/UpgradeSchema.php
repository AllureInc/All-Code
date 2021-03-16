<?php


namespace Kerastase\Core\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('customer_entity'),
                'is_encrypted',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'default'=> 0 ,
                    'nullable' => true,
                    'comment' => 'Is Data Encrypted'
                ]
            );


            $installer->getConnection()->addColumn(
                $installer->getTable('customer_address_entity'),
                'is_encrypted',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'default'=> 0 ,
                    'nullable' => true,
                    'comment' => 'Is Data Encrypted'
                ]
            );


        }
        $installer->endSetup();
    }
}