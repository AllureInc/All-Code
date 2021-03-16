<?php
/**
 * Copyright © 2015 Cor. All rights reserved.
 */

namespace Cor\Artistcategory\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * 
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * 
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {    
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'cor_artistcategory'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('cor_artistcategory')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'id'
        )
        ->addColumn(
            'category_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'category_name'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 1],
            'status'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false ,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'created_at'
        )
        ->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'updated_at'
        )
        ->setComment(
            'Cor Artist category table'
        );        
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
