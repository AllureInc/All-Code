<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Plenty\Core\Setup\SchemaInterface as PlentyCoreSchemaInterface;
use Plenty\Item\Api\Data\Export\CategoryInterface as ExportCategoryInterface;
use Plenty\Item\Api\Data\Export\ProductInterface as ExportProductInterface;
use Plenty\Item\Api\Data\Import\AttributeInterface as ImportAttributeInterface;
use Plenty\Item\Api\Data\Import\CategoryInterface as ImportCategoryInterface;
use Plenty\Item\Api\Data\Import\ItemInterface as ImportItemInterface;
use Plenty\Item\Api\Data\Import\Item\AttributeValueInterface as ImportItemAttributeValueInterface;
use Plenty\Item\Api\Data\Import\Item\BarcodeInterface as ImportItemBarcodeInterface;
use Plenty\Item\Api\Data\Import\Item\BundleInterface as ImportItemBundleInterface;
use Plenty\Item\Api\Data\Import\Item\CategoryInterface as ImportItemCategoryInterface;
use Plenty\Item\Api\Data\Import\Item\CrosssellsInterface as ImportItemCrosssellsInterface;
use Plenty\Item\Api\Data\Import\Item\MarketNumberInterface as ImportItemMarketNumberInterface;
use Plenty\Item\Api\Data\Import\Item\MediaInterface as ImportItemMediaInterface;
use Plenty\Item\Api\Data\Import\Item\PropertyInterface as ImportItemPropertyInterface;
use Plenty\Item\Api\Data\Import\Item\SalesPriceInterface as ImportItemSalesPriceInterface;
use Plenty\Item\Api\Data\Import\Item\StockInterface as ImportItemStockInterface;
use Plenty\Item\Api\Data\Import\Item\SupplierInterface as ImportItemSupplierInterface;
use Plenty\Item\Api\Data\Import\Item\TextsInterface as ImportItemTextsInterface;
use Plenty\Item\Api\Data\Import\Item\VariationInterface as ImportItemVariationInterface;
use Plenty\Item\Api\Data\Import\Item\WarehouseInterface as ImportItemWarehouseInterface;
use Plenty\Item\Api\Data\Import\Item\ShippingProfileInterface as ImportItemShippingProfileInterface;

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


        // Export entity tables
        $this->__createExportCategoryTable($installer);
        $this->__createExportProductTable($installer);

        // Import entity tables
        $this->__createImportAttributeTable($installer);
        $this->__createImportCategoryTable($installer);

        // Import item entity tables
        $this->__createImportItemTable($installer);
        $this->__createImportItemAttributeValueTable($installer);
        $this->__createImportItemBarcodeTable($installer);
        $this->__createImportItemBundleTable($installer);
        $this->__createImportItemCategoryTable($installer);
        $this->__createImportItemCrosssellsTable($installer);
        $this->__createImportItemMarketNumberTable($installer);
        $this->__createImportItemMediaTable($installer);
        $this->__createImportItemPropertyTable($installer);
        $this->__createImportItemSalesPriceTable($installer);
        $this->__createImportItemShippingProfileTable($installer);
        $this->__createImportItemStockTable($installer);
        $this->__createImportItemSupplierTable($installer);
        $this->__createImportItemTextsTable($installer);
        $this->__createImportItemWarehouseTable($installer);
        $this->__createImportItemVariationTable($installer);

        $installer->endSetup();
    }

    /**
     * Create table 'plenty_item_export_category'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createExportCategoryTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_EXPORT_CATEGORY);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ExportCategoryInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ExportCategoryInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ExportCategoryInterface::CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Category Id')
            ->addColumn(
                ExportCategoryInterface::PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Parent Id')
            ->addColumn(
                ExportCategoryInterface::SYSTEM_PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'System Path')
            ->addColumn(
                ExportCategoryInterface::CATEGORY_PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Category Path')
            ->addColumn(
                ExportCategoryInterface::CATEGORY_LEVEL,
                Table::TYPE_INTEGER,
                null,
                [],
                'Category Level')
            ->addColumn(
                ExportCategoryInterface::CHILDREN_COUNT,
                Table::TYPE_INTEGER,
                null,
                [],
                'Children Count')
            ->addColumn(
                ExportCategoryInterface::CATEGORY_NAME,
                Table::TYPE_TEXT,
                64,
                [],
                'Category Name')
            ->addColumn(
                ExportCategoryInterface::CATEGORY_ENTRIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Category Entries')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Plenty Category Id')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Plenty Category Parent Id')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_LEVEL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Plenty Category Level')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_HAS_CHILDREN,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0,],
                'Plenty Category Has Children')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Plenty Category Type')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_NAME,
                Table::TYPE_TEXT,
                255,
                [],
                'Plenty Category Name')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Plenty Category Path')
            ->addColumn(
                ExportCategoryInterface::PLENTY_CATEGORY_ENTRIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Plenty Category Entries')
            ->addColumn(
                ExportCategoryInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ExportCategoryInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ExportCategoryInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ExportCategoryInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ExportCategoryInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_CATEGORY, [ExportCategoryInterface::PROFILE_ID]),
                [ExportCategoryInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_CATEGORY, [ExportCategoryInterface::CATEGORY_ID]),
                [ExportCategoryInterface::CATEGORY_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_CATEGORY, [ExportCategoryInterface::CATEGORY_NAME]),
                [ExportCategoryInterface::CATEGORY_NAME])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_CATEGORY, [ExportCategoryInterface::PLENTY_CATEGORY_ID]),
                [ExportCategoryInterface::PLENTY_CATEGORY_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_EXPORT_CATEGORY,
                    [ExportCategoryInterface::PROFILE_ID, ExportCategoryInterface::CATEGORY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ExportCategoryInterface::PROFILE_ID, ExportCategoryInterface::CATEGORY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_EXPORT_CATEGORY, ExportCategoryInterface::PROFILE_ID, PlentyCoreSchemaInterface::CORE_PROFILE, ExportCategoryInterface::ENTITY_ID),
                ExportCategoryInterface::PROFILE_ID,
                $installer->getTable(PlentyCoreSchemaInterface::CORE_PROFILE),
                ExportCategoryInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Category Export Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_export_product'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createExportProductTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_EXPORT_PRODUCT);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ExportProductInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ExportProductInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ExportProductInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id')
            ->addColumn(
                ExportProductInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ExportProductInterface::NAME,
                Table::TYPE_TEXT,
                64,
                [],
                'Name')
            ->addColumn(
                ExportProductInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item ID')
            ->addColumn(
                ExportProductInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ExportProductInterface::MAIN_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Main Variation Id')
            ->addColumn(
                ExportProductInterface::TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Type')
            ->addColumn(
                ExportProductInterface::PRODUCT_TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Product Type')
            ->addColumn(
                ExportProductInterface::ATTRIBUTE_SET,
                Table::TYPE_TEXT,
                32,
                [],
                'Attribute Set')
            ->addColumn(
                ExportProductInterface::VISIBILITY,
                Table::TYPE_TEXT,
                32,
                [],
                'Visibility')
            ->addColumn(
                ExportProductInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ExportProductInterface::ENTRIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Entries')
            ->addColumn(
                ExportProductInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ExportProductInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ExportProductInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ExportProductInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_PRODUCT, [ExportProductInterface::PROFILE_ID]),
                [ExportProductInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_PRODUCT, [ExportProductInterface::PRODUCT_ID]),
                [ExportProductInterface::PRODUCT_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_EXPORT_PRODUCT, [ExportProductInterface::SKU]),
                [ExportProductInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_EXPORT_PRODUCT,
                    [ExportProductInterface::PROFILE_ID, ExportProductInterface::PRODUCT_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ExportProductInterface::PROFILE_ID, ExportProductInterface::PRODUCT_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_EXPORT_PRODUCT, ExportProductInterface::PROFILE_ID, PlentyCoreSchemaInterface::CORE_PROFILE, ExportProductInterface::ENTITY_ID),
                ExportProductInterface::PROFILE_ID,
                $installer->getTable(PlentyCoreSchemaInterface::CORE_PROFILE),
                ExportProductInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Product Export Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_attribute'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportAttributeTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ATTRIBUTE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportAttributeInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportAttributeInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ImportAttributeInterface::PROFILE_ATTRIBUTE_ID,
                Table::TYPE_TEXT,
                64,
                [],
                'Profile Attribute Id')
            ->addColumn(
                ImportAttributeInterface::TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Type')
            ->addColumn(
                ImportAttributeInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Position')
            ->addColumn(
                ImportAttributeInterface::ATTRIBUTE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Attribute Id')
            ->addColumn(
                ImportAttributeInterface::PROPERTY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Property Id')
            ->addColumn(
                ImportAttributeInterface::MANUFACTURER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Manufacturer Id')
            ->addColumn(
                ImportAttributeInterface::ATTRIBUTE_CODE,
                Table::TYPE_TEXT,
                32,
                [],
                'Attribute Code')
            ->addColumn(
                ImportAttributeInterface::PROPERTY_CODE,
                Table::TYPE_TEXT,
                32,
                [],
                'Property Code')
            ->addColumn(
                ImportAttributeInterface::PROPERTY_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Property Group Id')
            ->addColumn(
                ImportAttributeInterface::VALUE_TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Value Type')
            ->addColumn(
                ImportAttributeInterface::ENTRIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Entries')
            ->addColumn(
                ImportAttributeInterface::NAMES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Names')
            ->addColumn(
                ImportAttributeInterface::VALUES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Values')
            ->addColumn(
                ImportAttributeInterface::VALUE_NAMES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Value Names')
            ->addColumn(
                ImportAttributeInterface::SELECTIONS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Selections')
            ->addColumn(
                ImportAttributeInterface::MAPS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Maps')
            ->addColumn(
                ImportAttributeInterface::GROUP,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Group')
            ->addColumn(
                ImportAttributeInterface::MARKET_COMPONENTS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Market Components')
            ->addColumn(
                ImportAttributeInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ImportAttributeInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportAttributeInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportAttributeInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::PROFILE_ID]),
                [ImportAttributeInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::ATTRIBUTE_ID]),
                [ImportAttributeInterface::ATTRIBUTE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::PROPERTY_ID]),
                [ImportAttributeInterface::PROPERTY_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::MANUFACTURER_ID]),
                [ImportAttributeInterface::MANUFACTURER_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::ATTRIBUTE_CODE]),
                [ImportAttributeInterface::ATTRIBUTE_CODE])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, [ImportAttributeInterface::PROPERTY_CODE]),
                [ImportAttributeInterface::PROPERTY_CODE])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ATTRIBUTE,
                    [ImportAttributeInterface::PROFILE_ATTRIBUTE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportAttributeInterface::PROFILE_ATTRIBUTE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ATTRIBUTE, ImportAttributeInterface::PROFILE_ID, PlentyCoreSchemaInterface::CORE_PROFILE, ImportAttributeInterface::ENTITY_ID),
                ImportAttributeInterface::PROFILE_ID,
                $installer->getTable(PlentyCoreSchemaInterface::CORE_PROFILE),
                ImportAttributeInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Attribute Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_category'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportCategoryTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_CATEGORY);
        $this->dropTableIfExists($installer, $tableName);

        /**
         * Create table 'plenty_item_import_category'
         */
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportCategoryInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportCategoryInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile Id')
            ->addColumn(
                ImportCategoryInterface::CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Category Id')
            ->addColumn(
                ImportCategoryInterface::MAGE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Magento Category Id')
            ->addColumn(
                ImportCategoryInterface::PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Parent Id')
            ->addColumn(
                ImportCategoryInterface::LEVEL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Level')
            ->addColumn(
                ImportCategoryInterface::HAS_CHILDREN,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Has Children')
            ->addColumn(
                ImportCategoryInterface::TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Type')
            ->addColumn(
                ImportCategoryInterface::NAME,
                Table::TYPE_TEXT,
                255,
                [],
                'Name')
            ->addColumn(
                ImportCategoryInterface::PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Path')
            ->addColumn(
                ImportCategoryInterface::PREVIEW_URL,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Preview Url')
            ->addColumn(
                ImportCategoryInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ImportCategoryInterface::DETAILS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Details')
            ->addColumn(
                ImportCategoryInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ImportCategoryInterface::UPDATED_BY,
                Table::TYPE_TEXT,
                64,
                [],
                'Message')
            ->addColumn(
                ImportCategoryInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportCategoryInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportCategoryInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addColumn(
                ImportCategoryInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_CATEGORY, [ImportCategoryInterface::PROFILE_ID]),
                [ImportCategoryInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_CATEGORY, [ImportCategoryInterface::CATEGORY_ID]),
                [ImportCategoryInterface::CATEGORY_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_CATEGORY, [ImportCategoryInterface::MAGE_ID]),
                [ImportCategoryInterface::MAGE_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_CATEGORY,
                    [ImportCategoryInterface::PROFILE_ID, ImportCategoryInterface::CATEGORY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportCategoryInterface::PROFILE_ID, ImportCategoryInterface::CATEGORY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_CATEGORY, ImportCategoryInterface::PROFILE_ID, PlentyCoreSchemaInterface::CORE_PROFILE, ImportCategoryInterface::ENTITY_ID),
                ImportCategoryInterface::PROFILE_ID,
                $installer->getTable(PlentyCoreSchemaInterface::CORE_PROFILE),
                ImportCategoryInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Category Import Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Profile Id')
            ->addColumn(
                ImportItemInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ImportItemInterface::IS_ACTIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Active')
            ->addColumn(
                ImportItemInterface::ITEM_TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Item Type')
            ->addColumn(
                ImportItemInterface::PRODUCT_TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Product Type')
            ->addColumn(
                ImportItemInterface::BUNDLE_TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Bundle Type')
            ->addColumn(
                ImportItemInterface::STOCK_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Stock Type')
            ->addColumn(
                ImportItemInterface::ATTRIBUTE_SET,
                Table::TYPE_TEXT,
                32,
                [],
                'Attribute Set')
            ->addColumn(
                ImportItemInterface::FLAG_ONE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Flag One')
            ->addColumn(
                ImportItemInterface::FLAG_TWO,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Flag Two')
            ->addColumn(
                ImportItemInterface::POSITION,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true, 'nullable' => false, 'default' => 0],
                'Position')
            ->addColumn(
                ImportItemInterface::CUSTOMS_TARIFF_NO,
                Table::TYPE_TEXT,
                16,
                [],
                'Customs Tariff No')
            ->addColumn(
                ImportItemInterface::REVENUE_ACCOUNT,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true],
                'Revenue Account')
            ->addColumn(
                ImportItemInterface::CONDITION,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned'  => true],
                'Condition')
            ->addColumn(
                ImportItemInterface::CONDITION_API,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned'  => true],
                'Condition API')
            ->addColumn(
                ImportItemInterface::OWNER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Owner ID')
            ->addColumn(
                ImportItemInterface::MANUFACTURER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Manufacturer ID')
            ->addColumn(
                ImportItemInterface::MANUFACTURER_COUNTRY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Manufacturer Country ID')
            ->addColumn(
                ImportItemInterface::STORE_SPECIAL,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Special')
            ->addColumn(
                ImportItemInterface::COUPON_RESTRICTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Store Special')
            ->addColumn(
                ImportItemInterface::MAX_ORDER_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Max Order Qty')
            ->addColumn(
                ImportItemInterface::IS_SUBSCRIPTION,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Subscription')
            ->addColumn(
                ImportItemInterface::RAKUTEN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Rakuten Category Id')
            ->addColumn(
                ImportItemInterface::IS_SHIPPING_PACKAGE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Shipping Package')
            ->addColumn(
                ImportItemInterface::IS_SERIAL_NUMBER,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Serial number')
            ->addColumn(
                ImportItemInterface::AMAZON_FBA_PLATFORM,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true],
                'Amazon FBA Fulfilment')
            ->addColumn(
                ImportItemInterface::IS_SHIPPABLE_BY_AMAZON,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Shippable By Amazon')
            ->addColumn(
                ImportItemInterface::AMAZON_PRODUCT_TYPE,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true],
                'Amazon Product Type')
            ->addColumn(
                ImportItemInterface::AGE_RESTRICTION,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true],
                'Age Restriction')
            ->addColumn(
                ImportItemInterface::FEEDBACK,
                Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true],
                'Feedback')
            ->addColumn(
                ImportItemInterface::FREE1,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free1')
            ->addColumn(
                ImportItemInterface::FREE2,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free2')
            ->addColumn(
                ImportItemInterface::FREE3,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free3')
            ->addColumn(
                ImportItemInterface::FREE4,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free4')
            ->addColumn(
                ImportItemInterface::FREE5,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free5')
            ->addColumn(
                ImportItemInterface::FREE6,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free6')
            ->addColumn(
                ImportItemInterface::FREE7,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free7')
            ->addColumn(
                ImportItemInterface::FREE8,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free8')
            ->addColumn(
                ImportItemInterface::FREE9,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free9')
            ->addColumn(
                ImportItemInterface::FREE10,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Free10')
            ->addColumn(
                ImportItemInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ImportItemInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addColumn(
                ImportItemInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM, [ImportItemInterface::PROFILE_ID]),
                [ImportItemInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM, [ImportItemInterface::ITEM_ID]),
                [ImportItemInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM, [ImportItemInterface::VARIATION_ID]),
                [ImportItemInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM, [ImportItemInterface::SKU]),
                [ImportItemInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM, [ImportItemInterface::EXTERNAL_ID]),
                [ImportItemInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM,
                    [ImportItemInterface::PROFILE_ID, ImportItemInterface::ITEM_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemInterface::PROFILE_ID, ImportItemInterface::ITEM_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM, ImportItemInterface::PROFILE_ID, PlentyCoreSchemaInterface::CORE_PROFILE, ImportItemInterface::ENTITY_ID),
                ImportItemInterface::PROFILE_ID,
                $installer->getTable(PlentyCoreSchemaInterface::CORE_PROFILE),
                ImportItemInterface::ENTITY_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Entity');
        return $installer->getConnection()->createTable($table);
    }
    
    /**
     * Create table 'plenty_item_import_item_attribute_value'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemAttributeValueTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemAttributeValueInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemAttributeValueInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemAttributeValueInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemAttributeValueInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemAttributeValueInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_VALUE_SET_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Attribute Value Set ID')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Attribute ID')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Value ID')
            ->addColumn(
                ImportItemAttributeValueInterface::IS_LINKED_TO_IMAGE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Linked To Image')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_BACKEND_NAME,
                Table::TYPE_TEXT,
                64,
                [],
                'Attribute Backend Name')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Attribute Position')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_BACKEND_NAME,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Value Backend Name')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Value Position')

            ->addColumn(
                ImportItemAttributeValueInterface::IS_SURCHARGE_PERCENTAGE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Surcharge Percentage')
            ->addColumn(
                ImportItemAttributeValueInterface::AMAZON_ATTRIBUTE,
                Table::TYPE_TEXT,
                64,
                [],
                'Amazon Attribute Code')
            ->addColumn(
                ImportItemAttributeValueInterface::FRUUGO_ATTRIBUTE,
                Table::TYPE_TEXT,
                64,
                [],
                'Fruugo Attribute Code')
            ->addColumn(
                ImportItemAttributeValueInterface::PIXMANIA_ATTRIBUTE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Pixmania Attribute')
            ->addColumn(
                ImportItemAttributeValueInterface::OTTO_ATTRIBUTE,
                Table::TYPE_TEXT,
                64,
                [],
                'Otto Attribute Code')
            ->addColumn(
                ImportItemAttributeValueInterface::GOOGLE_SHOPPING_ATTRIBUTE,
                Table::TYPE_TEXT,
                64,
                [],
                'Google Shopping Attribute Code')
            ->addColumn(
                ImportItemAttributeValueInterface::NECKERMANN_AT_EP_ATTRIBUTE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Neckermann Attribute')
            ->addColumn(
                ImportItemAttributeValueInterface::TYPE_OF_SELECTION_IN_ONLINE_STORE,
                Table::TYPE_TEXT,
                16,
                [],
                'Selection type')
            ->addColumn(
                ImportItemAttributeValueInterface::LA_REDOUTE_ATTRIBUTE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'La Redoute Attribute')
            ->addColumn(
                ImportItemAttributeValueInterface::IS_GROUPABLE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Groupable')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_IMAGE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Value Image')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_COMMENT,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Value Comment')
            ->addColumn(
                ImportItemAttributeValueInterface::AMAZON_VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Amazon Value')
            ->addColumn(
                ImportItemAttributeValueInterface::OTTO_VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Otto Value')
            ->addColumn(
                ImportItemAttributeValueInterface::NECKERMANN_AT_EP_VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Neckermann Value')
            ->addColumn(
                ImportItemAttributeValueInterface::LA_REDOUTE_VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'La Redoute Value')
            ->addColumn(
                ImportItemAttributeValueInterface::TRACDELIGHT_VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Tracdelight Value')
            ->addColumn(
                ImportItemAttributeValueInterface::PERCENTAGE_DISTRIBUTION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Percentage Distribution')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Attribute Entry')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_VALUE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Attribute Value Entry')
            ->addColumn(
                ImportItemAttributeValueInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemAttributeValueInterface::ATTRIBUTE_UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Attribute Updated At')
            ->addColumn(
                ImportItemAttributeValueInterface::VALUE_UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Value Updated At')
            ->addColumn(
                ImportItemAttributeValueInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE, [ImportItemAttributeValueInterface::ITEM_ID]),
                [ImportItemAttributeValueInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE, [ImportItemAttributeValueInterface::VARIATION_ID]),
                [ImportItemAttributeValueInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE, [ImportItemAttributeValueInterface::EXTERNAL_ID]),
                [ImportItemAttributeValueInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE, [ImportItemAttributeValueInterface::SKU]),
                [ImportItemAttributeValueInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE,
                    [ImportItemAttributeValueInterface::VARIATION_ID, ImportItemAttributeValueInterface::ATTRIBUTE_ID, ImportItemAttributeValueInterface::VALUE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemAttributeValueInterface::VARIATION_ID, ImportItemAttributeValueInterface::ATTRIBUTE_ID, ImportItemAttributeValueInterface::VALUE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_ATTRIBUTE_VALUE, ImportItemAttributeValueInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemAttributeValueInterface::ITEM_ID),
                ImportItemAttributeValueInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemAttributeValueInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Attribute Value Entity');
        return $installer->getConnection()->createTable($table);
    }
    
    /**
     * Create table 'plenty_item_import_item_barcode'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemBarcodeTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemBarcodeInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemBarcodeInterface::BARCODE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Barcode Id')
            ->addColumn(
                ImportItemBarcodeInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemBarcodeInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemBarcodeInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemBarcodeInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemBarcodeInterface::CODE,
                Table::TYPE_TEXT,
                64,
                [],
                'Code')
            ->addColumn(
                ImportItemBarcodeInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemBarcodeInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemBarcodeInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, [ImportItemBarcodeInterface::ITEM_ID]),
                [ImportItemBarcodeInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, [ImportItemBarcodeInterface::VARIATION_ID]),
                [ImportItemBarcodeInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, [ImportItemBarcodeInterface::EXTERNAL_ID]),
                [ImportItemBarcodeInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, [ImportItemBarcodeInterface::SKU]),
                [ImportItemBarcodeInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, [ImportItemBarcodeInterface::CODE]),
                [ImportItemBarcodeInterface::CODE])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_BARCODE,
                    [ImportItemBarcodeInterface::VARIATION_ID, ImportItemBarcodeInterface::BARCODE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemBarcodeInterface::VARIATION_ID, ImportItemBarcodeInterface::BARCODE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_BARCODE, ImportItemBarcodeInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemBarcodeInterface::ITEM_ID),
                ImportItemBarcodeInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemBarcodeInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Barcode Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_bundle'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemBundleTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemBundleInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemBundleInterface::VARIATION_BUNDLE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Bundle Id')
            ->addColumn(
                ImportItemBundleInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemBundleInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemBundleInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemBundleInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemBundleInterface::COMPONENT_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Component Variation ID')
            ->addColumn(
                ImportItemBundleInterface::COMPONENT_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Component Qty')
            ->addColumn(
                ImportItemBundleInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemBundleInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemBundleInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE, [ImportItemBundleInterface::ITEM_ID]),
                [ImportItemBundleInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE, [ImportItemBundleInterface::VARIATION_ID]),
                [ImportItemBundleInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE, [ImportItemBundleInterface::EXTERNAL_ID]),
                [ImportItemBundleInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE, [ImportItemBundleInterface::SKU]),
                [ImportItemBundleInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE,
                    [ImportItemBundleInterface::VARIATION_BUNDLE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemBundleInterface::VARIATION_BUNDLE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_BUNDLE, ImportItemBundleInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemBundleInterface::ITEM_ID),
                ImportItemBundleInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemBundleInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Bundle Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_category'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemCategoryTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemCategoryInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemCategoryInterface::CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Category Id')
            ->addColumn(
                ImportItemCategoryInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemCategoryInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemCategoryInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemCategoryInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemCategoryInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Position')
            ->addColumn(
                ImportItemCategoryInterface::IS_NECKERMANN_PRIMARY,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Neckermann Primary')
            ->addColumn(
                ImportItemCategoryInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemCategoryInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemCategoryInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY, [ImportItemCategoryInterface::ITEM_ID]),
                [ImportItemCategoryInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY, [ImportItemCategoryInterface::VARIATION_ID]),
                [ImportItemCategoryInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY, [ImportItemCategoryInterface::EXTERNAL_ID]),
                [ImportItemCategoryInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY, [ImportItemCategoryInterface::SKU]),
                [ImportItemCategoryInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY,
                    [ImportItemCategoryInterface::VARIATION_ID, ImportItemCategoryInterface::CATEGORY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemCategoryInterface::VARIATION_ID, ImportItemCategoryInterface::CATEGORY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_CATEGORY, ImportItemCategoryInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemCategoryInterface::ITEM_ID),
                ImportItemCategoryInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemCategoryInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Category Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_crosssells'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemCrosssellsTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemCrosssellsInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemCrosssellsInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemCrosssellsInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemCrosssellsInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemCrosssellsInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemCrosssellsInterface::ITEM_CROSSSELLS_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Crosssell Id')
            ->addColumn(
                ImportItemCrosssellsInterface::RELATIONSHIP,
                Table::TYPE_TEXT,
                16,
                [],
                'Relationship')
            ->addColumn(
                ImportItemCrosssellsInterface::IS_DYNAMIC,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned'  => true, 'nullable'  => true],
                'Is Dynamic')
            ->addColumn(
                ImportItemCrosssellsInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemCrosssellsInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemCrosssellsInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, [ImportItemCrosssellsInterface::ITEM_ID]),
                [ImportItemCrosssellsInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, [ImportItemCrosssellsInterface::VARIATION_ID]),
                [ImportItemCrosssellsInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, [ImportItemCrosssellsInterface::EXTERNAL_ID]),
                [ImportItemCrosssellsInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, [ImportItemCrosssellsInterface::SKU]),
                [ImportItemCrosssellsInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, [ImportItemCrosssellsInterface::ITEM_CROSSSELLS_ID]),
                [ImportItemCrosssellsInterface::ITEM_CROSSSELLS_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS,
                    [ImportItemCrosssellsInterface::VARIATION_ID, ImportItemCrosssellsInterface::ITEM_CROSSSELLS_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemCrosssellsInterface::VARIATION_ID, ImportItemCrosssellsInterface::ITEM_CROSSSELLS_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_CROSSSELLS, ImportItemCrosssellsInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemCrosssellsInterface::ITEM_ID),
                ImportItemCrosssellsInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemCrosssellsInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Crosssells Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_media'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemMediaTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemMediaInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemMediaInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemMediaInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemMediaInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemMediaInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemMediaInterface::MEDIA_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Media Id')
            ->addColumn(
                ImportItemMediaInterface::MEDIA_TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Media Type')
            ->addColumn(
                ImportItemMediaInterface::IS_LINKED_TO_VARIATION,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Linked to Variation')
            ->addColumn(
                ImportItemMediaInterface::TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'Type')
            ->addColumn(
                ImportItemMediaInterface::FILE_TYPE,
                Table::TYPE_TEXT,
                32,
                [],
                'File Type')
            ->addColumn(
                ImportItemMediaInterface::PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Path')
            ->addColumn(
                ImportItemMediaInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Position')
            ->addColumn(
                ImportItemMediaInterface::MD5_CHECKSUM,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Md5 Check Sum')
            ->addColumn(
                ImportItemMediaInterface::MD5_CHECKSUM_ORIGINAL,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Md5 Check Sum Original')
            ->addColumn(
                ImportItemMediaInterface::WIDTH,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Width')
            ->addColumn(
                ImportItemMediaInterface::HEIGHT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Height')
            ->addColumn(
                ImportItemMediaInterface::SIZE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Size')
            ->addColumn(
                ImportItemMediaInterface::STORAGE_PROVIDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Storage Provider Id')
            ->addColumn(
                ImportItemMediaInterface::CLEAN_IMAGE_NAME,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Clean Image Name')
            ->addColumn(
                ImportItemMediaInterface::URL,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Url')
            ->addColumn(
                ImportItemMediaInterface::URL_MIDDLE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Url Middle')
            ->addColumn(
                ImportItemMediaInterface::URL_PREVIEW,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Url Preview')
            ->addColumn(
                ImportItemMediaInterface::URL_SECONDARY_PREVIEW,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Url Secondary Preview')
            ->addColumn(
                ImportItemMediaInterface::DOCUMENT_UPLOAD_PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Document Upload Path')
            ->addColumn(
                ImportItemMediaInterface::DOCUMENT_UPLOAD_PATH_PREVIEW,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Document Upload Path Preview')
            ->addColumn(
                ImportItemMediaInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Document Upload Preview Width')
            ->addColumn(
                ImportItemMediaInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Document Upload Preview Height')
            ->addColumn(
                ImportItemMediaInterface::AVAILABILITIES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Availabilities')
            ->addColumn(
                ImportItemMediaInterface::NAMES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Names')
            ->addColumn(
                ImportItemMediaInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemMediaInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemMediaInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, [ImportItemMediaInterface::ITEM_ID]),
                [ImportItemMediaInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, [ImportItemMediaInterface::VARIATION_ID]),
                [ImportItemMediaInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, [ImportItemMediaInterface::EXTERNAL_ID]),
                [ImportItemMediaInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, [ImportItemMediaInterface::SKU]),
                [ImportItemMediaInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, [ImportItemMediaInterface::MEDIA_ID]),
                [ImportItemMediaInterface::MEDIA_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_MEDIA,
                    [ImportItemMediaInterface::MEDIA_ID, ImportItemMediaInterface::VARIATION_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemMediaInterface::MEDIA_ID, ImportItemMediaInterface::VARIATION_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_MEDIA, ImportItemMediaInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemMediaInterface::ITEM_ID),
                ImportItemMediaInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemMediaInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Media Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_market_number'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemMarketNumberTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemMarketNumberInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemMarketNumberInterface::PLENTY_ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Market Number Id')
            ->addColumn(
                ImportItemMarketNumberInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemMarketNumberInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemMarketNumberInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemMarketNumberInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemMarketNumberInterface::COUNTRY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Country Id')
            ->addColumn(
                ImportItemMarketNumberInterface::TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Type')
            ->addColumn(
                ImportItemMarketNumberInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Position')
            ->addColumn(
                ImportItemMarketNumberInterface::VALUE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Value')
            ->addColumn(
                ImportItemMarketNumberInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemMarketNumberInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemMarketNumberInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER, [ImportItemMarketNumberInterface::ITEM_ID]),
                [ImportItemMarketNumberInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER, [ImportItemMarketNumberInterface::VARIATION_ID]),
                [ImportItemMarketNumberInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER, [ImportItemMarketNumberInterface::EXTERNAL_ID]),
                [ImportItemMarketNumberInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER, [ImportItemMarketNumberInterface::SKU]),
                [ImportItemMarketNumberInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER,
                    [ImportItemMarketNumberInterface::VARIATION_ID, ImportItemMarketNumberInterface::PLENTY_ENTITY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemMarketNumberInterface::VARIATION_ID, ImportItemMarketNumberInterface::PLENTY_ENTITY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_MARKET_NUMBER, ImportItemMarketNumberInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemMarketNumberInterface::ITEM_ID),
                ImportItemMarketNumberInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemMarketNumberInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Market Number Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_property'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemPropertyTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemPropertyInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemPropertyInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemPropertyInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemPropertyInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemPropertyInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemPropertyInterface::PLENTY_ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Property Id')
            ->addColumn(
                ImportItemPropertyInterface::PROPERTY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Property Id')
            ->addColumn(
                ImportItemPropertyInterface::PROPERTY_SELECTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Property Selection Id')
            ->addColumn(
                ImportItemPropertyInterface::NAMES,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Names')
            ->addColumn(
                ImportItemPropertyInterface::PROPERTY_SELECTION,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Property Selection')
            ->addColumn(
                ImportItemPropertyInterface::PROPERTY,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Property Entries')
            ->addColumn(
                ImportItemPropertyInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemPropertyInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemPropertyInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::ITEM_ID]),
                [ImportItemPropertyInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::VARIATION_ID]),
                [ImportItemPropertyInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::EXTERNAL_ID]),
                [ImportItemPropertyInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::SKU]),
                [ImportItemPropertyInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::PROPERTY_ID]),
                [ImportItemPropertyInterface::PROPERTY_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, [ImportItemPropertyInterface::PROPERTY_SELECTION_ID]),
                [ImportItemPropertyInterface::PROPERTY_SELECTION_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY,
                    [ImportItemPropertyInterface::PLENTY_ENTITY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemPropertyInterface::PLENTY_ENTITY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_PROPERTY, ImportItemPropertyInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemPropertyInterface::ITEM_ID),
                ImportItemPropertyInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemPropertyInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Property Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_sales_price'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemSalesPriceTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemSalesPriceInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemSalesPriceInterface::SALES_PRICE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Sales Price Id')
            ->addColumn(
                ImportItemSalesPriceInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemSalesPriceInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemSalesPriceInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemSalesPriceInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemSalesPriceInterface::PRICE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable'  => false, 'default'   => 0.0000],
                'Price')
            ->addColumn(
                ImportItemSalesPriceInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemSalesPriceInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemSalesPriceInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE, [ImportItemSalesPriceInterface::ITEM_ID]),
                [ImportItemSalesPriceInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE, [ImportItemSalesPriceInterface::VARIATION_ID]),
                [ImportItemSalesPriceInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE, [ImportItemSalesPriceInterface::EXTERNAL_ID]),
                [ImportItemSalesPriceInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE, [ImportItemSalesPriceInterface::SKU]),
                [ImportItemSalesPriceInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE,
                    [ImportItemSalesPriceInterface::VARIATION_ID, ImportItemSalesPriceInterface::SALES_PRICE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemSalesPriceInterface::VARIATION_ID, ImportItemSalesPriceInterface::SALES_PRICE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_SALES_PRICE, ImportItemSalesPriceInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemSalesPriceInterface::ITEM_ID),
                ImportItemSalesPriceInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemSalesPriceInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Sales Price Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_shipping_profile'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemShippingProfileTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemShippingProfileInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemShippingProfileInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemShippingProfileInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemShippingProfileInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemShippingProfileInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemShippingProfileInterface::PLENTY_ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Shipping Profile Entity ID')
            ->addColumn(
                ImportItemShippingProfileInterface::PROFILE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Profile ID')
            ->addColumn(
                ImportItemShippingProfileInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemShippingProfileInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemShippingProfileInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, [ImportItemShippingProfileInterface::ITEM_ID]),
                [ImportItemShippingProfileInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, [ImportItemShippingProfileInterface::VARIATION_ID]),
                [ImportItemShippingProfileInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, [ImportItemShippingProfileInterface::EXTERNAL_ID]),
                [ImportItemShippingProfileInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, [ImportItemShippingProfileInterface::SKU]),
                [ImportItemShippingProfileInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, [ImportItemShippingProfileInterface::PROFILE_ID]),
                [ImportItemShippingProfileInterface::PROFILE_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE,
                    [ImportItemShippingProfileInterface::VARIATION_ID, ImportItemShippingProfileInterface::PLENTY_ENTITY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemShippingProfileInterface::VARIATION_ID, ImportItemShippingProfileInterface::PLENTY_ENTITY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_SHIPPING_PROFILE, ImportItemShippingProfileInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemShippingProfileInterface::ITEM_ID),
                ImportItemShippingProfileInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemShippingProfileInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Shipping Profile Entity');
        return $installer->getConnection()->createTable($table);
    }
    
    /**
     * Create table 'plenty_item_import_item_stock'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemStockTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_STOCK);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemStockInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemStockInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemStockInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemStockInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemStockInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemStockInterface::WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Warehouse Id')
            ->addColumn(
                ImportItemStockInterface::PURCHASE_PRICE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable'  => false, 'default' => 0.0000],
                'Purchase Price')
            ->addColumn(
                ImportItemStockInterface::RESERVED_LISTING,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Reserved Listing')
            ->addColumn(
                ImportItemStockInterface::RESERVED_BUNDLES,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Reserved Bundle')
            ->addColumn(
                ImportItemStockInterface::PHYSICAL_STOCK,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Physical Stock')
            ->addColumn(
                ImportItemStockInterface::RESERVED_STOCK,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Reserved Stock')
            ->addColumn(
                ImportItemStockInterface::NET_STOCK,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Net Stock')
            ->addColumn(
                ImportItemStockInterface::REORDER_LEVEL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Reorder Level')
            ->addColumn(
                ImportItemStockInterface::DELTA_REORDER_LEVEL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Delta Reorder Level')
            ->addColumn(
                ImportItemStockInterface::GOODS_VALUE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable'  => false, 'default' => 0.0000],
                'Value of goods')
            ->addColumn(
                ImportItemStockInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemStockInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemStockInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, [ImportItemStockInterface::ITEM_ID]),
                [ImportItemStockInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, [ImportItemStockInterface::VARIATION_ID]),
                [ImportItemStockInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, [ImportItemStockInterface::EXTERNAL_ID]),
                [ImportItemStockInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, [ImportItemStockInterface::SKU]),
                [ImportItemStockInterface::SKU])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, [ImportItemStockInterface::WAREHOUSE_ID]),
                [ImportItemStockInterface::WAREHOUSE_ID])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_STOCK,
                    [ImportItemStockInterface::VARIATION_ID, ImportItemStockInterface::WAREHOUSE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemStockInterface::VARIATION_ID, ImportItemStockInterface::WAREHOUSE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, ImportItemStockInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemStockInterface::ITEM_ID),
                ImportItemStockInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemStockInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Stock Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_supplier'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemSupplierTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemSupplierInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemSupplierInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemSupplierInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemSupplierInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemSupplierInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemSupplierInterface::PLENTY_ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Plenty Entity Id')
            ->addColumn(
                ImportItemSupplierInterface::SUPPLIER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Supplier Id')
            ->addColumn(
                ImportItemSupplierInterface::PURCHASE_PRICE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Purchase Price')
            ->addColumn(
                ImportItemSupplierInterface::MINIMUM_PURCHASE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Supplier Id')
            ->addColumn(
                ImportItemSupplierInterface::ITEM_NUMBER,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Item Number')
            ->addColumn(
                ImportItemSupplierInterface::LAST_PRICE_QUERY,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Last Price Query')
            ->addColumn(
                ImportItemSupplierInterface::DELIVERY_TIME_IN_DAYS,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Delivery Times In Days')
            ->addColumn(
                ImportItemSupplierInterface::DISCOUNT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Discount')
            ->addColumn(
                ImportItemSupplierInterface::IS_DISCOUNTABLE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Discountable')
            ->addColumn(
                ImportItemSupplierInterface::PACKAGING_UNIT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1],
                'Packaging Unit')
            ->addColumn(
                ImportItemSupplierInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemSupplierInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemSupplierInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER, [ImportItemSupplierInterface::ITEM_ID]),
                [ImportItemSupplierInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER, [ImportItemSupplierInterface::VARIATION_ID]),
                [ImportItemSupplierInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER, [ImportItemSupplierInterface::EXTERNAL_ID]),
                [ImportItemSupplierInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER, [ImportItemSupplierInterface::SKU]),
                [ImportItemSupplierInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER,
                    [ImportItemSupplierInterface::VARIATION_ID, ImportItemSupplierInterface::PLENTY_ENTITY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemSupplierInterface::VARIATION_ID, ImportItemSupplierInterface::PLENTY_ENTITY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_SUPPLIER, ImportItemSupplierInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemSupplierInterface::ITEM_ID),
                ImportItemSupplierInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemSupplierInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Market Number Entity');
        return $installer->getConnection()->createTable($table);
    }
    
    /**
     * Create table 'plenty_item_import_item_texts'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemTextsTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemTextsInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemTextsInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemTextsInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemTextsInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemTextsInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemTextsInterface::PLENTY_ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Plenty Entity Id')
            ->addColumn(
                ImportItemTextsInterface::LANG,
                Table::TYPE_TEXT,
                8,
                [],
                'Language Code')
            ->addColumn(
                ImportItemTextsInterface::NAME,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Name')
            ->addColumn(
                ImportItemTextsInterface::NAME2,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Name 2')
            ->addColumn(
                ImportItemTextsInterface::NAME3,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Name 3')
            ->addColumn(
                ImportItemTextsInterface::SHORT_DESCRIPTION,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Short Description')
            ->addColumn(
                ImportItemTextsInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Description')
            ->addColumn(
                ImportItemTextsInterface::TECHNICAL_DATA,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Technical Data')
            ->addColumn(
                ImportItemTextsInterface::URL_PATH,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'URL Path')
            ->addColumn(
                ImportItemTextsInterface::META_DESCRIPTION,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Meta Description')
            ->addColumn(
                ImportItemTextsInterface::META_KEYWORDS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Meta Keywords')
            ->addColumn(
                ImportItemTextsInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemTextsInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemTextsInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS, [ImportItemTextsInterface::ITEM_ID]),
                [ImportItemTextsInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS, [ImportItemTextsInterface::VARIATION_ID]),
                [ImportItemTextsInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS, [ImportItemTextsInterface::EXTERNAL_ID]),
                [ImportItemTextsInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS, [ImportItemTextsInterface::SKU]),
                [ImportItemTextsInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_TEXTS,
                    [ImportItemTextsInterface::VARIATION_ID, ImportItemTextsInterface::LANG],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemTextsInterface::VARIATION_ID, ImportItemTextsInterface::LANG],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_TEXTS, ImportItemTextsInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemTextsInterface::ITEM_ID),
                ImportItemTextsInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemTextsInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Texts Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_warehouse'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemWarehouseTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemWarehouseInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemWarehouseInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Id')
            ->addColumn(
                ImportItemWarehouseInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Id')
            ->addColumn(
                ImportItemWarehouseInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemWarehouseInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemWarehouseInterface::WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Warehouse ID')
            ->addColumn(
                ImportItemWarehouseInterface::WAREHOUSE_ZONE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Warehouse Zone ID')
            ->addColumn(
                ImportItemWarehouseInterface::STORAGE_LOCATION_TYPE,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Storage Location Type')
            ->addColumn(
                ImportItemWarehouseInterface::REORDER_LEVEL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Reorder Level')
            ->addColumn(
                ImportItemWarehouseInterface::MAX_STOCK,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Maximum Stock')
            ->addColumn(
                ImportItemWarehouseInterface::STOCK_TURNOVER_IN_DAYS,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Stock Turnover In Days')
            ->addColumn(
                ImportItemWarehouseInterface::STORAGE_LOCATION,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Storage Location')
            ->addColumn(
                ImportItemWarehouseInterface::STOCK_BUFFER,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Stock Buffer')
            ->addColumn(
                ImportItemWarehouseInterface::IS_BATCH,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Batch')
            ->addColumn(
                ImportItemWarehouseInterface::IS_BEST_BEFORE_DATE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Best Before Date')
            ->addColumn(
                ImportItemWarehouseInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemWarehouseInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemWarehouseInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE, [ImportItemWarehouseInterface::ITEM_ID]),
                [ImportItemWarehouseInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE, [ImportItemWarehouseInterface::VARIATION_ID]),
                [ImportItemWarehouseInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE, [ImportItemWarehouseInterface::EXTERNAL_ID]),
                [ImportItemWarehouseInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE, [ImportItemWarehouseInterface::SKU]),
                [ImportItemWarehouseInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE,
                    [ImportItemWarehouseInterface::VARIATION_ID, ImportItemWarehouseInterface::WAREHOUSE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemWarehouseInterface::VARIATION_ID, ImportItemWarehouseInterface::WAREHOUSE_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_WAREHOUSE, ImportItemWarehouseInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemWarehouseInterface::ITEM_ID),
                ImportItemWarehouseInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemWarehouseInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Market Number Entity');
        return $installer->getConnection()->createTable($table);
    }

    /**
     * Create table 'plenty_item_import_item_variation'
     *
     * @param SchemaSetupInterface $installer
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    private function __createImportItemVariationTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION);
        $this->dropTableIfExists($installer, $tableName);

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                ImportItemVariationInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id')
            ->addColumn(
                ImportItemVariationInterface::ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Item Id')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Variation Id')
            ->addColumn(
                ImportItemVariationInterface::MAIN_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Main Variation Id')
            ->addColumn(
                ImportItemVariationInterface::EXTERNAL_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'External Id')
            ->addColumn(
                ImportItemVariationInterface::SKU,
                Table::TYPE_TEXT,
                64,
                [],
                'SKU')
            ->addColumn(
                ImportItemVariationInterface::NAME,
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [],
                'Name')
            ->addColumn(
                ImportItemVariationInterface::STATUS,
                Table::TYPE_TEXT,
                16,
                [],
                'Status')
            ->addColumn(
                ImportItemVariationInterface::IS_MAIN,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Main')
            ->addColumn(
                ImportItemVariationInterface::IS_ACTIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Active')
            ->addColumn(
                ImportItemVariationInterface::CATEGORY_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Category Variation Id')
            ->addColumn(
                ImportItemVariationInterface::MARKET_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Market Variation Id')
            ->addColumn(
                ImportItemVariationInterface::CLIENT_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Client Variation Id')
            ->addColumn(
                ImportItemVariationInterface::SALES_PRICE_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Sales Price Variation Id')
            ->addColumn(
                ImportItemVariationInterface::SUPPLIER_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Supplier Variation Id')
            ->addColumn(
                ImportItemVariationInterface::WAREHOUSE_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Warehouse Variation Id')
            ->addColumn(
                ImportItemVariationInterface::PROPERTY_VARIATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Property Variation Id')
            ->addColumn(
                ImportItemVariationInterface::POSITION,
                Table::TYPE_SMALLINT,
                null,
                [],
                'Position')
            ->addColumn(
                ImportItemVariationInterface::MODEL,
                Table::TYPE_TEXT,
                64,
                [],
                'Model')
            ->addColumn(
                ImportItemVariationInterface::PARENT_VARIATION_ID,
                Table::TYPE_SMALLINT,
                null,
                [],
                'Parent Variation Id')
            ->addColumn(
                ImportItemVariationInterface::PARENT_VARIATION_QTY,
                Table::TYPE_SMALLINT,
                null,
                [],
                'Parent Variation Qty')
            ->addColumn(
                ImportItemVariationInterface::AVAILABILITY,
                Table::TYPE_SMALLINT,
                null,
                [],
                'Availability')
            ->addColumn(
                ImportItemVariationInterface::FLAG_ONE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'FlagOne')
            ->addColumn(
                ImportItemVariationInterface::FLAG_TWO,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'FlagTwo')
            ->addColumn(
                ImportItemVariationInterface::ESTIMATED_AVAILABLE_AT,
                Table::TYPE_TEXT,
                16,
                [],
                'Estimated Available At')
            ->addColumn(
                ImportItemVariationInterface::PURCHASE_PRICE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Purchase Price')
            ->addColumn(
                ImportItemVariationInterface::RELATED_UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Related Updated At')
            ->addColumn(
                ImportItemVariationInterface::PRICE_CALCULATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Price Calculation Id')
            ->addColumn(
                ImportItemVariationInterface::PICKING,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Picking')
            ->addColumn(
                ImportItemVariationInterface::STOCK_LIMITATION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Stock Limitation')
            ->addColumn(
                ImportItemVariationInterface::IS_VISIBLE_IF_NET_STOCK_IS_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Visible If Net Stock Is Positive')
            ->addColumn(
                ImportItemVariationInterface::IS_INVISIBLE_IF_NET_STOCK_IS_NOT_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Visible If Net Stock Is Not Positive')
            ->addColumn(
                ImportItemVariationInterface::IS_AVAILABLE_IF_NET_STOCK_IS_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Available If Net Stock Is Positive')
            ->addColumn(
                ImportItemVariationInterface::IS_UNAVAILABLE_IF_NET_STOCK_IS_NOT_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Unavailable If Net Stock Is Not Possible')
            ->addColumn(
                ImportItemVariationInterface::MAIN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Main Warehouse Id')
            ->addColumn(
                ImportItemVariationInterface::MAX_ORDER_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Max Order Qty')
            ->addColumn(
                ImportItemVariationInterface::MIN_ORDER_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Min Order Qty')
            ->addColumn(
                ImportItemVariationInterface::INTERVAL_ORDER_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Interval Order Qty')
            ->addColumn(
                ImportItemVariationInterface::AVAILABLE_UNTIL,
                Table::TYPE_TEXT,
                16,
                [],
                'Available Unit')
            ->addColumn(
                ImportItemVariationInterface::RELEASED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Released At')
            ->addColumn(
                ImportItemVariationInterface::UNIT_COMBINATION_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Unit Combination Id')
            ->addColumn(
                ImportItemVariationInterface::WEIGHT_G,
                Table::TYPE_TEXT,
                8,
                [],
                'Weight G')
            ->addColumn(
                ImportItemVariationInterface::WEIGHT_NET_G,
                Table::TYPE_TEXT,
                8,
                [],
                'Weight Net G')
            ->addColumn(
                ImportItemVariationInterface::WIDTH_MM,
                Table::TYPE_TEXT,
                8,
                [],
                'Width MM')
            ->addColumn(
                ImportItemVariationInterface::LENGTH_MM,
                Table::TYPE_TEXT,
                8,
                [],
                'Length MM')
            ->addColumn(
                ImportItemVariationInterface::HEIGHT_MM,
                Table::TYPE_TEXT,
                8,
                [],
                'Height MM')
            ->addColumn(
                ImportItemVariationInterface::EXTRA_SHIPPING_CHARGES1,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Extra Shipping Charges1')
            ->addColumn(
                ImportItemVariationInterface::EXTRA_SHIPPING_CHARGES2,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Extra Shipping Charges2')
            ->addColumn(
                ImportItemVariationInterface::UNITS_CONTAINED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1],
                'Units Contained')
            ->addColumn(
                ImportItemVariationInterface::PALLET_TYPE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Pellet Type Id')
            ->addColumn(
                ImportItemVariationInterface::PACKING_UNITS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Packing Units')
            ->addColumn(
                ImportItemVariationInterface::PACKING_UNITS_TYPE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Packing Units Type Id')
            ->addColumn(
                ImportItemVariationInterface::TRANSPORTATION_COSTS,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Transportation Cost')
            ->addColumn(
                ImportItemVariationInterface::STORAGE_COSTS,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Storage Cost')
            ->addColumn(
                ImportItemVariationInterface::CUSTOMS,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Customs Rate')
            ->addColumn(
                ImportItemVariationInterface::OPERATION_COSTS,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Operation Cost')
            ->addColumn(
                ImportItemVariationInterface::VAT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'VAT ID')
            ->addColumn(
                ImportItemVariationInterface::BUNDLE_TYPE,
                Table::TYPE_TEXT,
                16,
                [],
                'Bundle Type')
            ->addColumn(
                ImportItemVariationInterface::AUTO_CLIENT_VISIBILITY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Auto Client Visibility')
            ->addColumn(
                ImportItemVariationInterface::IS_HIDDEN_IN_CATEGORY_LIST,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Hidden In Category List')
            ->addColumn(
                ImportItemVariationInterface::DEFAULT_SHIPPING_COSTS,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Default Shipping Cost')
            ->addColumn(
                ImportItemVariationInterface::CAN_SHOW_UNIT_PRICE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Can Show Units Price')
            ->addColumn(
                ImportItemVariationInterface::MOVING_AVERAGE_PRICE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0.0000],
                'Moving Average Price')
            ->addColumn(
                ImportItemVariationInterface::AUTO_LIST_VISIBILITY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Auto List Visibility')
            ->addColumn(
                ImportItemVariationInterface::IS_VISIBLE_IN_LIST_IF_NET_STOCK_IS_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Visible In List If Net Stock Is Positive')
            ->addColumn(
                ImportItemVariationInterface::IS_INVISIBLE_IN_LIST_IF_NET_STOCK_IS_NOT_POSITIVE,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Invisible In List If Net Stock Is Not Positive')
            ->addColumn(
                ImportItemVariationInterface::SINGLE_ITEM_COUNT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Single Item Count')
            ->addColumn(
                ImportItemVariationInterface::SALES_RANK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Sales Rank')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_CLIENTS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Variation Clients')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_MARKETS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Variation Markets')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_DEFAULT_CATEGORY,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Default Category')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_SKUS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Variation Skus')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_ADDITIONAL_SKUS,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Variation Additional Skus')
            ->addColumn(
                ImportItemVariationInterface::VARIATION_UNIT,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Variation Unit')
            ->addColumn(
                ImportItemVariationInterface::MESSAGE,
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [],
                'Message')
            ->addColumn(
                ImportItemVariationInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At')
            ->addColumn(
                ImportItemVariationInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated At')
            ->addColumn(
                ImportItemVariationInterface::COLLECTED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Collected At')
            ->addColumn(
                ImportItemVariationInterface::PROCESSED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Processed At')
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, [ImportItemVariationInterface::ITEM_ID]),
                [ImportItemVariationInterface::ITEM_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, [ImportItemVariationInterface::VARIATION_ID]),
                [ImportItemVariationInterface::VARIATION_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, [ImportItemVariationInterface::EXTERNAL_ID]),
                [ImportItemVariationInterface::EXTERNAL_ID])
            ->addIndex(
                $installer->getIdxName(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, [ImportItemVariationInterface::SKU]),
                [ImportItemVariationInterface::SKU])
            ->addIndex(
                $installer->getIdxName(
                    SchemaInterface::ITEM_IMPORT_ITEM_VARIATION,
                    [ImportItemVariationInterface::VARIATION_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE),
                [ImportItemVariationInterface::VARIATION_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE])
            ->addForeignKey(
                $installer->getFkName(SchemaInterface::ITEM_IMPORT_ITEM_VARIATION, ImportItemVariationInterface::ITEM_ID, SchemaInterface::ITEM_IMPORT_ITEM, ImportItemVariationInterface::ITEM_ID),
                ImportItemVariationInterface::ITEM_ID,
                $installer->getTable(SchemaInterface::ITEM_IMPORT_ITEM),
                ImportItemVariationInterface::ITEM_ID,
                Table::ACTION_CASCADE)
            ->setComment(
                'Item Import Variation Entity');
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