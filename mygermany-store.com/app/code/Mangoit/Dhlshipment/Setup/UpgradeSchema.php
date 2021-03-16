<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited
 */

namespace Mangoit\Dhlshipment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup,ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion() , '1.0.1') < 0){
            // Get module table
            $tableName = $setup->getTable('marketplace_orders');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $setup->getConnection()->addColumn($tableName,'vendor_shipped_by',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 100,
                        'nullable' => true,
                        'afters' => 'tracking_number',
                        'comment' => 'Vendor Shipped by DHL & Own Delivery Method'
                    ]
                );               

            }

        }

        $setup->endSetup();

    }

}

