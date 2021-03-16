<?php
/**
 * This file execute:
 * when the current version number in the setup_module table is lower than the version configured
 * in your etc/module.xml file
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Setup;
use Magento\Framework\Db\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Ui\Component\Layout\Tabs\Tab;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tableName = $installer->getTable('solrbridge_custom_sample');

        if (version_compare($context->getVersion(), '1.0.1', '<'))
        {
            $newColumn = [];
        }

        $installer->getConnection()->addColumn(
            $tableName,
            'content',
            ['type' => Table::TYPE_BLOB, 'nullable' => true, 'comment' => 'Content', 'default' => NULL]
        );

        $installer->endSetup();
    }
}