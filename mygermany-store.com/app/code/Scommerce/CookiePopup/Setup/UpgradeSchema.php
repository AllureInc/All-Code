<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;
use Scommerce\CookiePopup\Model\Data\Choice;
use Scommerce\CookiePopup\Model\Data\Link;

/**
 * Class InstallSchema
 * @package Scommerce\CookiePopup\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
    * {@inheritdoc}
    *
    * @param SchemaSetupInterface $setup
    * @param ModuleContextInterface $context
    * @throws \Zend_Db_Exception
    * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
    */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //code to upgrade to 1.0.1
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable(ChoiceInterface::TABLE),
                    ChoiceInterface::DEFAULT_STATE,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Set By Default'
                    ]
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable(LinkInterface::TABLE),
                    LinkInterface::LINK_ID,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'unsigned' => true,
                        'primary' => true,
                        'identity' => true,
                        'comment' => 'Link Id'
                    ]
                );

            /**
             * Create table 'scommerce_cookie_popup_choice_store'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable(ChoiceInterface::TABLE_STORE)
            )->addColumn(
                ChoiceInterface::CHOICE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true, 'unsigned' => true],
                'Choice ID'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex(
                $installer->getIdxName(ChoiceInterface::TABLE_STORE, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(ChoiceInterface::TABLE_STORE, ChoiceInterface::CHOICE_ID, ChoiceInterface::TABLE, ChoiceInterface::CHOICE_ID),
                ChoiceInterface::CHOICE_ID,
                $installer->getTable(ChoiceInterface::TABLE),
                ChoiceInterface::CHOICE_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(ChoiceInterface::TABLE_STORE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Cookie Choice To Store Linkage Table'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
