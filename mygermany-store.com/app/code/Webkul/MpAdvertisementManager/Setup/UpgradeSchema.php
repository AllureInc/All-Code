<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            /*
            * Create table 'marketplace_ads_block'
            */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('marketplace_ads_purchase_details'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order Id'
                )->addColumn(
                    'seller_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Seller Id'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )->addColumn(
                    'item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Item Id'
                )->addColumn(
                    'block_position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'ads position'
                )->addColumn(
                    'block',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'block'
                )->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'ads price'
                )->addColumn(
                    'block_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '55',
                    ['nullable' => false, 'default' => '0'],
                    'Block name'
                )->addColumn(
                    'valid_for',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'ads valid days'
                )->addColumn(
                    'enable',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false,'default'=>1],
                    'Enable/Disable from admin'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )->addColumn(
                    'store_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '55',
                    ['nullable' => false, 'default' => '0'],
                    'Store name'
                )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Creation Time'
                )->addColumn(
                    'invoice_generated',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false,'default'=>0],
                    'Invoice generated or not ?'
                )->addForeignKey(
                    $setup->getFkName(
                        'marketplace_ads_purchase_details',
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment('marketplace ads block pricing');
            $setup->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('marketplace_ads_purchase_block_details'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'ads_purchase_detail_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Ads purchase detail ID'
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order Id'
                )->addColumn(
                    'image_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'name of image'
                )->addColumn(
                    'title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'block title'
                )->addColumn(
                    'url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'ads url'
                )->addForeignKey(
                    $setup->getFkName(
                        'marketplace_ads_purchase_block_details',
                        'ads_purchase_detail_id',
                        'marketplace_ads_purchase_details',
                        'id'
                    ),
                    'ads_purchase_detail_id',
                    $setup->getTable('marketplace_ads_purchase_details'),
                    'id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment('marketplace ads block html');
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
