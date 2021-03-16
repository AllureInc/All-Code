<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

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
            $installer->getTable('wk_amazon_mapped_product'),
            'feedsubmission_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'feedsubmission id of exported product of amazon'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'product_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon product sku'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'export_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'export status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'pro_status_at_amz',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'exported product status'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_mapped_product'),
            'error_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon product status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_maped_order'),
            'purchase_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length' => null,
                'nullable' => false,
                'comment' => 'purchase date'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_tempdata'),
            'purchase_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length' => null,
                'nullable' => false,
                'comment' => 'purchase date'
            ]
        );

        // create extra fields in seller account table
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_cate',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default category for product assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_store_view',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default store view for product and order assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'product_create',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'product create with or without variation'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'default_website',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'default website for product assignment'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'order_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'order status of amazon order'
            ]
        );
        // for product api
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'associate_tag',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'order status of amazon order'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'pro_api_secret_key',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'secret key of product api'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_accounts'),
            'pro_api_access_key_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'access key of product api'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('wk_amazon_tempdata'),
            'product_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'amazon seller sku'
            ]
        );

                /*
        * Create table 'wk_amazon_pricerule
        */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_amazon_pricerule'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Entity Id'
            )->addColumn(
                'price_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product price from'
            )->addColumn(
                'price_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product price to'
            )->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product sku'
            )->addColumn(
                'operation',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'product price operation'
            )->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Price'
            )->addColumn(
                'operation_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product operation type ex. fixed/percent'
            )->addColumn(
                'amz_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],
                'amazon account id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],
                'status of rule'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'rule created time'
            )->addIndex(
                $setup->getIdxName('wk_ebay_pricerule', ['entity_id']),
                ['entity_id']
            )->setComment('eBay Product Price Rule');

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
