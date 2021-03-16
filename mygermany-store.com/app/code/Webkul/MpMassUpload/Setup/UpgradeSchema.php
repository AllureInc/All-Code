<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->moveDirToMediaDir();
        $setup->startSetup();
        $setup->getConnection()->dropColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'is_new'
        );
        $setup->getConnection()->dropColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'csv_file'
        );
        $setup->getConnection()->dropColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'status'
        );
        $setup->getConnection()->dropColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'time'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'created_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => false,
                'comment' => 'Created Date'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'image_file',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Image Folder Name'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'link_file',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Link Folder Name'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'sample_file',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Sample Folder Name'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'data_row',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
                'length' => '64m',
                'nullable' => false,
                'comment' => 'Uploaded File Whole Row Serialed Data'
            ]
        );

        /**
         * Update tables 'marketplace_massupload_profile'
         */
        $setup->getConnection()->changeColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'data_row',
            'data_row',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
                'length' => '64m',
                'nullable' => false,
                'comment' => 'Uploaded File Whole Row Serialed Data'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_massupload_profile'),
            'file_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'File Type'
            ]
        );

        /*
         * Create table 'marketplace_massupload_attribute_profile'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_massupload_attribute_profile'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Seller Id'
            )
            ->addColumn(
                'profile_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Profile Name to uniquely identify each uploaded file'
            )
            ->addColumn(
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Attribute Set Id'
            )
            ->addColumn(
                'created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Date'
            )
            ->setComment('Mass Upload Attribute Mapping Profile Table');
        $setup->getConnection()->createTable($table);

        /*
         * Create table 'marketplace_massupload_attribute_mapping'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('marketplace_massupload_attribute_mapping')
        )
        ->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Profile Id'
        )
        ->addColumn(
            'file_attribute',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Uploaded File Attribute'
        )
        ->addColumn(
            'mage_attribute',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Mage Origional Attribute Name'
        )->addIndex(
            $setup->getIdxName('marketplace_massupload_attribute_mapping', ['profile_id']),
            ['profile_id']
        )->addForeignKey(
            $setup->getFkName(
                'marketplace_massupload_attribute_mapping',
                'profile_id',
                'marketplace_massupload_attribute_profile',
                'entity_id'
            ),
            'profile_id',
            $setup->getTable('marketplace_massupload_attribute_profile'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Mass Upload Attribute Mapping Table'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }

    private function moveDirToMediaDir()
    {
        try {
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');
            $filesystem = $objManager->get('Magento\Framework\Filesystem');
            $fileDriver = $objManager->get('Magento\Framework\Filesystem\Driver\File');
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $smpleFilePath = $filesystem->getDirectoryRead($type)
                                        ->getAbsolutePath().'marketplace/massupload/samples/';
            $files = ['simple.csv', 'downloadable.csv', 'config.csv', 'virtual.csv','simple.xml', 'downloadable.xml', 'config.xml', 'virtual.xml', 'simple.xls', 'downloadable.xls', 'config.xls', 'virtual.xls'];
            if ($fileDriver->isExists($smpleFilePath)) {
                $fileDriver->deleteDirectory($smpleFilePath);
            }
            if (!$fileDriver->isExists($smpleFilePath)) {
                $fileDriver->createDirectory($smpleFilePath, 0777);
            }
            foreach ($files as $file) {
                $filePath = $smpleFilePath.$file;
                if (!$fileDriver->isExists($filePath)) {
                    $path = '/pub/media/marketplace/massupload/samples/'.$file;
                    $mediaFile = $reader->getModuleDir('', 'Webkul_MpMassUpload').$path;
                    if ($fileDriver->isExists($mediaFile)) {
                        $fileDriver->copy($mediaFile, $filePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}
