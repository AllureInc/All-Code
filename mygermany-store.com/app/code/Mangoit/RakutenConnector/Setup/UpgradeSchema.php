<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Setup;

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
        $installer->getConnection()->addColumn(
            $installer->getTable('marketplace_saleslist'),
            'is_rakuten',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 2,
                'nullable' => true,
                'comment' => 'is rakuten order'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('marketplace_orders'),
            'is_rakuten',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 2,
                'nullable' => true,
                'comment' => 'is rakuten order'
            ]
        );

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('marketplace_rakuten_accounts'),
                'default_tax_class_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 2,
                    'nullable' => true,
                    'comment' => 'Default tax class id.'
                ]
            );
        }
        $installer->endSetup();
    }
}
