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
namespace Mangoit\Advertisement\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;
        $installer->startSetup();
        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $installer->getConnection()
            ->addColumn(
                $installer->getTable( 'marketplace_ads_pricing' ),
                'ad_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => '0 External, 1 Internal',
                    'after' => 'website_id',
                    'default'=>'0'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_pricing' ),
                'content_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => '1 image, 2 product, 3 category, 4 html',
                    'after' => 'website_id',
                    'default'=>'1'
                ]
            );

            $installer->getConnection()
            ->addColumn(
                $installer->getTable( 'marketplace_ads_block' ),
                'ad_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => '0 External, 1 Internal',
                    'after' => 'seller_id',
                    'default'=>'0'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_block' ),
                'content_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => '1 image, 2 product, 3 category, 4 html',
                    'after' => 'ad_type',
                    'default'=>'1'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_block' ),
                'product_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'product id',
                    'after' => 'content_type',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_block' ),
                'category_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'category id',
                    'after' => 'content_type',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'content_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => '1 image, 2 product, 3 category, 4 html',
                    'after' => 'product_id',
                    'default'=>'1'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'category_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'category id',
                    'after' => 'product_id',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'selected_product',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'category id',
                    'after' => 'product_id',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'block_position',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'block_position',
                    'after' => 'product_id',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '64',
                    'comment' => 'store_id id',
                    'after' => 'product_id',
                    'default'=>'null'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'block_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Block name',
                    'after' => 'product_id'
                ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'block_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Block name',
                    'after' => 'product_id'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'store_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Store name',
                    'after' => 'store_id'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'store_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Store name',
                    'after' => 'store_id'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'valid_for',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'valid for',
                    'after' => 'store_id'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'enable',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Enable',
                    'after' => 'added_by'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable( 'mis_admin_ads' ),
                'webkul_block_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '256',
                    'comment' => 'Enable',
                    'after' => 'enable'
                ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'mis_approval_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 2,
                    'comment' => 'Approval Status',
                    'default'=>0
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'mis_approval_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment' => 'Approval Date'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'marketplace_ads_purchase_details' ),
                'mis_approval_decline_msg',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Decline Message',
                    'default'=> null
                ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.5', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'quote_item' ),
                'custom_base_price_for_adv_product',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base price for Advertisement Product. Used in case of currency conversion.'
                ]
            );
        }
        $installer->endSetup();
    }
 
}
