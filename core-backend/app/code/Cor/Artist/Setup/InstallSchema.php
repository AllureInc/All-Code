<?php
/**
 * Copyright © 2015 Cor. All rights reserved.
 */
namespace Cor\Artist\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {    
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'cor_artist'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('cor_artist')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'cor_artist_id'
        )
        ->addColumn(
            'artist_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'magento_artist_id'
        )
        ->addColumn(
            'artist_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'artist_name'
        )
        ->addColumn(
            'artist_rep_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'artist_rep_name'
        )
        ->addColumn(
            'artist_rep_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'artist_rep_number'
        )
        ->addColumn(
            'artist_rep_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'artist_rep_email'
        )
        ->addColumn(
            'artist_tax_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '2M',
            ['nullable' => false, 'default' => '0'],
            'artist_tax_id'
        )
        ->addColumn(
            'wnine_received',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'wnine_received'
        )
        ->setComment(
            'Cor Artist artist_artist'
        );        
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
