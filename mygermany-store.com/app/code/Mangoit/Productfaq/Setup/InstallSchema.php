<?php
/**
 * Mangoit  Software.
 *
 * @category  Mangoit 
 * @package   Mangoit
 * @author    Mangoit 
 */

namespace Mangoit\Productfaq\Setup;

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
         * updating table 'ced_productfaq'
         */
        $table = $installer->getConnection()->addColumn(
                $installer->getTable('ced_productfaq'),
                'vendor_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => 'Vendor Id'
                ]
            );

        $installer->endSetup();
    }
}
