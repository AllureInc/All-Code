<?php
/**
 * This file execute: when there is no record in the setup_module table for the module
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Db\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('solrbridge_custom_sample');
        $table = $installer->getConnection()->newTable($tableName);

        //addColumn (columnname, type, size, options, description);

        $table->addColumn(
            'sample_id',
            \Magento\Framework\Db\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Sample Id'
        );

        $table->addColumn(
            'title',
            \Magento\Framework\Db\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        );

        $table->addColumn(
            'title',
            \Magento\Framework\Db\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        );

        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('solrbridge_custom_sample'),
                ['title'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        );

        $table->setComment('Sample Demo Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}