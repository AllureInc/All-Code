<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Solrbridge\Search\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

use Solrbridge\Search\Model\Index;

/**
 * Upgrade the CatalogRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addColumnDoctype($setup);
            $this->addColumnBoostWeightIntoAttributeTable($setup);
        }
        
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->addColumnIsMultipleFilterIntoAttributeTable($setup);
        }
        
        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->addColumnRenderAsDropdownIntoAttributeTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add new column doctype into table index
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addColumnDoctype(SchemaSetupInterface $setup)
    {
        /*
        $connection = $setup->getConnection();
        
        $setup->getConnection()->addColumn(
            $setup->getTable('solrbridge_search_index'),
            'doctype',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => Index::DOCTYPE_PRODUCT,
                'comment' => 'Doctype'
            ]
        );
        */
    }
    
    private function addColumnBoostWeightIntoAttributeTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('catalog_eav_attribute'),
            'solrbridge_search_boost_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Solrbridge search boost weight'
            ]
        );
    }
    
    private function addColumnIsMultipleFilterIntoAttributeTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('catalog_eav_attribute'),
            'solrbridge_search_multiple_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Solrbridge search is Attribute multiple filter'
            ]
        );
    }
    
    private function addColumnRenderAsDropdownIntoAttributeTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('catalog_eav_attribute'),
            'solrbridge_search_render_as_dropdown',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Solrbridge search render attribute as dropdown in Layer Nav'
            ]
        );
    }
}
