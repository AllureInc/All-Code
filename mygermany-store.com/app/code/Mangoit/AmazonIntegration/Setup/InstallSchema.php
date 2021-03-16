<?php
/**
 * Mangoit  Software.
 *
 * @category  Mangoit 
 * @package   Mangoit_AmazonIntegration
 * @author    Mangoit 
 */

namespace Mangoit\AmazonIntegration\Setup;

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
         * updating table 'wk_amazon_accounts'
         */
        $table = $installer->getConnection()->addColumn(
                $installer->getTable('wk_amazon_accounts'),
                'magento_seller_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => 'Magento seller ID'
                ]
            );

        $installer->endSetup();
    }
}
