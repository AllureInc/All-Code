<?php
/**
 * Mangoit  Software.
 *
 * @category  Mangoit 
 * @package   Webkul_Marketplace
 * @author    Mangoit 
 * @copyright Copyright (c) 2010-2018 Mangoit  Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Marketplace\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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
         * updating table 'marketplace_userdata'
         */
        $table = $installer->getConnection()->addColumn(
                $installer->getTable('marketplace_userdata'),
                'vendor_shop_layout',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Vendor Shop Layout'
                ]
            );

        $installer->endSetup();
    }
}
