<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

use Plenty\Core\Api\Data\AuthInterface;
use Plenty\Core\Api\Data\ProfileInterface;
use Plenty\Core\Api\Data\Profile\ConfigInterface as ProfileConfigInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface as ProfileHistoryInterface;
use Plenty\Core\Api\Data\Profile\ScheduleInterface as ProfileScheduleInterface;
use Plenty\Core\Api\Data\Config\SourceInterface as ConfigSourceInterface;

/**
 * Class InstallSchema
 * @package Plenty\Core\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->__createAuthTable($installer);
        $this->__createProfileTable($installer);
        $this->__createProfileConfigTable($installer);
        $this->__createProfileHistoryTable($installer);
        $this->__createProfileScheduleTable($installer);
        $this->__createConfigSourceTable($installer);

        $installer->endSetup();
    }

    /**
     * Create table 'plenty_core_auth'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createAuthTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_AUTH);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                AuthInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                AuthInterface::TOKEN_TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Token Type')
            ->addColumn(
                AuthInterface::TOKEN_EXPIRES_IN,
                Table::TYPE_TEXT,
                32,
                [],
                'Expires In')
            ->addColumn(
                AuthInterface::ACCESS_TOKEN,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Access Token')
            ->addColumn(
                AuthInterface::REFRESH_TOKEN,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Refresh Token')
            ->addColumn(
                AuthInterface::LICENSE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'License')
            ->addColumn(
                AuthInterface::DOMAIN,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Domain')
            ->addColumn(
                AuthInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                AuthInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                AuthInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->setComment(
                'Auth Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_core_profile'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createProfileTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_PROFILE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ProfileInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ProfileInterface::NAME,
                Table::TYPE_TEXT,
                32,
                [],
                'Name')
            ->addColumn(
                ProfileInterface::IS_ACTIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Active')
            ->addColumn(
                ProfileInterface::ENTITY,
                Table::TYPE_TEXT,
                64,
                [],
                'Entity')
            ->addColumn(
                ProfileInterface::ADAPTOR,
                Table::TYPE_TEXT,
                32,
                [],
                'Adaptor')
            ->addColumn(
                ProfileInterface::CRONTAB,
                Table::TYPE_TEXT,
                16,
                [],
                'CronTab')
            ->addColumn(
                ProfileInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ProfileInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ProfileInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->setComment(
                'Profile Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_core_profile_config'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createProfileConfigTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_PROFILE_CONFIG);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ProfileConfigInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ProfileConfigInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ProfileConfigInterface::SCOPE,
                Table::TYPE_TEXT,
                8,
                ['nullable' => false, 'default' => 'default'],
                'Config Scope')
            ->addColumn(
                ProfileConfigInterface::SCOPE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Config Scope Id')
            ->addColumn(
                ProfileConfigInterface::PATH,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'general'],
                'Config Path')
            ->addColumn(
                ProfileConfigInterface::VALUE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Config Value')
            ->addColumn(
                ProfileConfigInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ProfileConfigInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::CORE_PROFILE_CONFIG,
                    [ProfileConfigInterface::PROFILE_ID, ProfileConfigInterface::SCOPE, ProfileConfigInterface::SCOPE_ID, ProfileConfigInterface::PATH],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ProfileConfigInterface::PROFILE_ID, ProfileConfigInterface::SCOPE, ProfileConfigInterface::SCOPE_ID, ProfileConfigInterface::PATH],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::CORE_PROFILE_CONFIG, ProfileConfigInterface::PROFILE_ID, SchemaInterface::CORE_PROFILE, ProfileConfigInterface::ENTITY_ID),
                ProfileConfigInterface::PROFILE_ID,
                $installer->getTable(SchemaInterface::CORE_PROFILE),
                ProfileConfigInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Profile Config');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_core_profile_history'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createProfileHistoryTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_PROFILE_HISTORY);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ProfileHistoryInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ProfileHistoryInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ProfileHistoryInterface::ACTION_CODE,
                Table::TYPE_TEXT,
                64,
                [],
                'Action Code')
            ->addColumn(
                ProfileHistoryInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ProfileHistoryInterface::PARAMS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Params')
            ->addColumn(
                ProfileHistoryInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ProfileHistoryInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ProfileHistoryInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::CORE_PROFILE_HISTORY, [ProfileHistoryInterface::PROFILE_ID]),
                [ProfileHistoryInterface::PROFILE_ID])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::CORE_PROFILE_HISTORY, ProfileHistoryInterface::PROFILE_ID, SchemaInterface::CORE_PROFILE, ProfileHistoryInterface::ENTITY_ID),
                ProfileHistoryInterface::PROFILE_ID,
                $installer->getTable(SchemaInterface::CORE_PROFILE),
                ProfileHistoryInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Profile History');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_core_profile_schedule'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createProfileScheduleTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_PROFILE_SCHEDULE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ProfileScheduleInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ProfileScheduleInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ProfileScheduleInterface::STATUS,
                Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'pending'],
                'Status')
            ->addColumn(
                ProfileScheduleInterface::JOB_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '0'],
                'Job Code')
            ->addColumn(
                ProfileScheduleInterface::PARAMS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Params')
            ->addColumn(
                ProfileScheduleInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ProfileScheduleInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ProfileScheduleInterface::SCHEDULED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Scheduled At')
            ->addColumn(
                ProfileScheduleInterface::EXECUTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Executed At')
            ->addColumn(
                ProfileScheduleInterface::FINISHED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Finished At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::CORE_PROFILE_SCHEDULE, [ProfileScheduleInterface::PROFILE_ID]),
                [ProfileScheduleInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::CORE_PROFILE_SCHEDULE, [ProfileScheduleInterface::JOB_CODE]),
                [ProfileScheduleInterface::JOB_CODE])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::CORE_PROFILE_SCHEDULE, [ProfileScheduleInterface::SCHEDULED_AT, ProfileScheduleInterface::STATUS]),
                [ProfileScheduleInterface::SCHEDULED_AT, ProfileScheduleInterface::STATUS])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::CORE_PROFILE_SCHEDULE, ProfileScheduleInterface::PROFILE_ID, SchemaInterface::CORE_PROFILE, ProfileScheduleInterface::ENTITY_ID),
                ProfileScheduleInterface::PROFILE_ID,
                $installer->getTable(SchemaInterface::CORE_PROFILE),
                ProfileHistoryInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Profile Schedule');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_core_config_source'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createConfigSourceTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::CORE_CONFIG_SOURCE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ConfigSourceInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ConfigSourceInterface::ENTRY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Entry Id')
            ->addColumn(
                ConfigSourceInterface::CONFIG_SOURCE,
                Table::TYPE_TEXT,
                64,
                ['unsigned' => true],
                'Config Source')
            ->addColumn(
                ConfigSourceInterface::CONFIG_ENTRIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Config Entries')
            ->addColumn(
                ConfigSourceInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ConfigSourceInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Updated At')
            ->addColumn(
                ConfigSourceInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::CORE_CONFIG_SOURCE, [ConfigSourceInterface::CONFIG_SOURCE]),
                [ConfigSourceInterface::CONFIG_SOURCE])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::CORE_CONFIG_SOURCE,
                    [ConfigSourceInterface::ENTRY_ID, ConfigSourceInterface::CONFIG_SOURCE],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ConfigSourceInterface::ENTRY_ID, ConfigSourceInterface::CONFIG_SOURCE],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->setComment(
                'Config Source');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param $table
     */
    private function dropTableIfExists(SchemaSetupInterface $installer, $table)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable($table))) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }
    }
}