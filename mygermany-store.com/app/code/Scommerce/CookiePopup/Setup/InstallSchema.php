<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Db\Adapter\AdapterInterface;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Class InstallSchema
 * @package Scommerce\CookiePopup\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tables = [
            $this->getChoiceTable($installer, $setup),
            $this->getLinkTable($installer, $setup),
        ];

        foreach ($tables as $table) {
            /* @var Table $table */
            if (! $installer->getConnection()->isTableExists($table->getName())) {
                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function getChoiceTable(SchemaSetupInterface $installer, SchemaSetupInterface $setup)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(ChoiceInterface::TABLE)
        )->addColumn(
            ChoiceInterface::CHOICE_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Choice ID'
        )->addColumn(
            ChoiceInterface::CHOICE_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Choice Name'
        )->addColumn(
            ChoiceInterface::CHOICE_DESCRIPTION,
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Choice Description'
        )->addColumn(
            ChoiceInterface::COOKIE_NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Cookie Name'
        )->addColumn(
            ChoiceInterface::CHOICE_LIST,
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Choice List'
        )->addColumn(
            ChoiceInterface::REQUIRED,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Required'
        )->addIndex(
            $installer->getIdxName(ChoiceInterface::TABLE, [ChoiceInterface::CHOICE_NAME]),
            [ChoiceInterface::CHOICE_NAME]
        )->addIndex(
            $installer->getIdxName(ChoiceInterface::TABLE, [ChoiceInterface::COOKIE_NAME]),
            [ChoiceInterface::COOKIE_NAME]
        )->addIndex(
            $installer->getIdxName(ChoiceInterface::TABLE, [ChoiceInterface::REQUIRED]),
            [ChoiceInterface::REQUIRED]
        )->setComment('Scommerce Cookie Popup Choices');
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param SchemaSetupInterface $setup
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function getLinkTable(SchemaSetupInterface $installer, SchemaSetupInterface $setup)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable(LinkInterface::TABLE)
        )->addColumn(
            LinkInterface::CUSTOMER_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            LinkInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->addColumn(
            LinkInterface::CHOICE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Choice ID'
        )->addColumn(
            LinkInterface::STATUS,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Status [0/1] [Accept/Decline]'
        )->addColumn(
            LinkInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Choice Customer Creation Time'
        )->addColumn(
            LinkInterface::UPDATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Choice Customer Modification Time'
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::CHOICE_ID, LinkInterface::STORE_ID, LinkInterface::CUSTOMER_ID]),
            [LinkInterface::CHOICE_ID, LinkInterface::STORE_ID, LinkInterface::CUSTOMER_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::CUSTOMER_ID]),
            [LinkInterface::CUSTOMER_ID]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::STORE_ID]),
            [LinkInterface::STORE_ID]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::CHOICE_ID]),
            [LinkInterface::CHOICE_ID]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::STATUS]),
            [LinkInterface::STATUS]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::CREATED_AT]),
            [LinkInterface::CREATED_AT]
        )->addIndex(
            $installer->getIdxName(LinkInterface::TABLE, [LinkInterface::UPDATED_AT]),
            [LinkInterface::UPDATED_AT]
        )->addForeignKey(
            $installer->getFkName(LinkInterface::TABLE, LinkInterface::CUSTOMER_ID, 'customer_entity', 'entity_id'),
            LinkInterface::CUSTOMER_ID,
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(LinkInterface::TABLE, LinkInterface::STORE_ID, 'store', 'store_id'),
            LinkInterface::STORE_ID,
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(LinkInterface::TABLE, LinkInterface::CHOICE_ID, ChoiceInterface::TABLE, ChoiceInterface::CHOICE_ID),
            LinkInterface::CHOICE_ID,
            $installer->getTable(ChoiceInterface::TABLE),
            ChoiceInterface::CHOICE_ID,
            Table::ACTION_CASCADE
        )->setComment('Scommerce Cookie Popup Choices Links');
    }
}
