<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited
 */

namespace Mangoit\VendorPayments\Setup; 
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup2
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup2 = $setup2;
    }


    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_saleslist'),
                'mits_payment_fee_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Payment fee deducted from the vendor\'s payable amount.'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_saleslist'),
                'mits_exchange_rate_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Currency Exchange Charge deducted from the vendor\'s payable amount.'
                ]
            );

            /*
             * Create table 'mits_currency_exchange_charge'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mits_currency_exchange_charge'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'base_to_target_currency',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'From and to Currency'
                )
                // ->addColumn(
                //     'charge_percent',
                //     \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                //     null,
                //     [
                //         'length' => '12,4',
                //         'nullable' => false,
                //         'default' => '0'
                //     ],
                //     'Charge in percent'
                // )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Update Time'
                )
                ->setComment('Currency Exchange Rate Charges.');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('mits_currency_exchange_charge'),
                'charge_percent',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Charge in percent.'
                ]
            );
            $installer->endSetup();
        }


        if (version_compare($context->getVersion(), '0.0.4') < 0) {
            /**
             * Update tables 'marketplace_saleslist'
             */
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_saleslist'),
                'is_item_invoiced',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Invoiced'
                ]
            );

            /*
             * Create table 'mits_vendor_invoices'
             */
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mits_vendor_invoices'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Invoice ID'
                )
                ->addColumn(
                    'seller_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Seller ID'
                )
                ->addColumn(
                    'saleslist_item_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Invoiced item record ids'
                )
                ->addColumn(
                    'order_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Invoiced order ids'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Creation Time'
                )
                ->setComment('Vendor Invoice Data.');
            $setup->getConnection()->createTable($table);

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.5') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('mits_vendor_invoices'),
                'invoice_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'system generated invoice number.'
                ]
            );
        }
    }
}
