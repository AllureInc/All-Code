<?php
/**
 * Mangoit  Software.
 *
 * @category  Mangoit 
 * @package   Mangoit_NewsletterCustom
 * @author    Mangoit 
 * @copyright Copyright (c) 2010-2018 Mangoit
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\NewsletterCustom\Setup;

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
                $installer->getTable('newsletter_template'),
                'mailchimp_temp_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'after' => 'template_id',
                    'comment' => 'Template Id of Mailchimp'
                ]
            );

        $installer->endSetup();
    }
}
