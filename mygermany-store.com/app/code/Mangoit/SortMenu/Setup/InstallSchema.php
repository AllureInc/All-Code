<?php
namespace Mangoit\SortMenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface 
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) 
    {
        $setup->startSetup();
        $setup->getConnection()
        ->changeColumn(
            $setup->getTable('catalog_product_entity'), 
            'sku', 
            'sku', 
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 
                'length' => 255
            ]
        );
    }
}
