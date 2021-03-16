<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /*
         * Create table 'marketplace_rakuten_accounts'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('marketplace_rakuten_accounts'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false,'primary' => true],
                'Entity Id'
            )->addColumn(
                'attribute_set',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'attribute set id'
            )/*->addColumn(
                'amz_seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Rakuten seller name'
            )->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Magento Product Id'
            )*/->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Seller Id'
            )/*->addColumn(
                'marketplace_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Rakuten Marketplace Id'
            )->addColumn(
                'access_key_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'AWS Access Key ID'
            )*/->addColumn(
                'secret_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Secret Key'
            )/*->addColumn(
                'currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Currency code'
            )*/->addColumn(
                'revise_item',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'enable or disable revise item'
            )/*->addColumn(
                'listing_report_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Generated seller listing report id'
            )->addColumn(
                'inventory_report_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Generated seller inventory report id'
            )*/->addColumn(
                'default_cate',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default category'
            )->addColumn(
                'default_store_view',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default store view'
            )->addColumn(
                'product_create',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'product create as simple or configurable'
            )->addColumn(
                'default_website',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default website for account'
            )->addColumn(
                'order_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default order status for account'
            )->addColumn(
                'default_pro_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default product quantity for account'
            )->addColumn(
                'default_pro_weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'default product weight for account'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Rakuten account add time'
            )->addIndex(
                $installer->getIdxName('marketplace_rakuten_accounts', ['entity_id']),
                ['entity_id']
            )->setComment('Marketplace Rakuten Accounts');

        $installer->getConnection()->createTable($table);

        /*
         * Create table 'marketplace_rakuten_mapped_product'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('marketplace_rakuten_mapped_product'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'magento_pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Product Id'
            )->addColumn(
                'rakuten_pro_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Rakuten Product Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Product Name'
            )->addColumn(
                'product_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product Type'
            )->addColumn(
                'mage_cat_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Category Id'
            )->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'seller id'
            )->addColumn(
                'rkt_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rakuten product id'
            )->addColumn(
                'feedsubmission_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'feedsubmission id of exported product of amazon'
            )->addColumn(
                'export_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'export status'
            )->addColumn(
                'error_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'rakuten product status'
            )->addColumn(
                'pro_status_at_rkt',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'product status at rakuten'
            )->addColumn(
                'assign',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'assign in any category or not'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'rakuten seller sku'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Product sync Time'
            )->addIndex(
                $installer->getIdxName('marketplace_rakuten_mapped_product', ['entity_id']),
                ['entity_id']
            )->setComment('Rakuten Mapped Product');

        $installer->getConnection()->createTable($table);


        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_rakuten_mapped_order'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'rakuten_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Rakuten Order Id'
            )->addColumn(
                'mage_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Magento Order Id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Order Status'
            )->addColumn(
                'assign',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'assigned in category'
            )->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'seller id'
            )->addColumn(
                'purchase_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Order Place date at amazon'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Order Sync Time'
            )->addIndex(
                $installer->getIdxName('marketplace_rakuten_mapped_order', ['entity_id']),
                ['entity_id']
            )->setComment('Rakuten Synchronize Order Table');

        $installer->getConnection()->createTable($table);

        /*
         * Create table 'marketplace_rakuten_tempdata'
         */

        $table = $installer->getConnection()->newTable($installer->getTable('marketplace_rakuten_tempdata'))
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
                'item_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Idenityfy that order or product'
            )->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Rakuten Item Id'
            )->addColumn(
                'item_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4M',
                [],
                'Rakuten item data in json format'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Import Time'
            )->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'seller id'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'amazon seller sku'
            )->addColumn(
                'rkt_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rakuten product id'
            )->addIndex(
                $installer->getIdxName('marketplace_rakuten_tempdata', ['entity_id']),
                ['entity_id']
            )->setComment('Rakuten imported products/order temp table');

        $installer->getConnection()->createTable($table);
        /****/
        $installer->endSetup();
        $this->addForeignKeys($setup);
    }

    public function addForeignKeys($setup)
    {
        /**
         * Add foreign keys for table marketplace_rakuten_mapped_product
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_rakuten_mapped_product',
                'magento_pro_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $setup->getTable('marketplace_rakuten_mapped_product'),
            'magento_pro_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
