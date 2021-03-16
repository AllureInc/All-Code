<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Solrbridge\Search\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Solrbridge\Search\Model\Index;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'wishlist'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('solrbridge_search_index')
        )->addColumn(
            'index_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Index ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store ID'
        )->addColumn(
            'solr_core',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'solr core like english, french, german...'
        )->addColumn(
            'doctype',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => Index::DOCTYPE_PRODUCT],
            'Document Type'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Index Title...'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last updated date'
        )->addIndex(
            $installer->getIdxName(
                'index',
                'store_id',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'store_id',
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'SolrBridge Index Management Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
