<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->moveDirToMediaDir();
        $installer = $setup;

        $installer->startSetup();
        /**
         * Create table 'wk_ws_wallet_record'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_ws_wallet_record'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'customer ID'
            )
            ->addColumn(
                'total_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Total Amount'
            )
            ->addColumn(
                'remaining_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Remaining Amount'
            )
            ->addColumn(
                'used_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Used Amount'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )
            
            ->setComment('Webkul wallet record table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'wk_ws_wallet_transaction'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_ws_wallet_transaction'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer ID'
            )
            ->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Amount'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Transaction status'
            )
            ->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Amount Action'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Order Id'
            )
            ->addColumn(
                'transaction_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Transaction At'
            )
            ->addColumn(
                'currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Currency Code'
            )
            ->addColumn(
                'curr_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Currency Amount'
            )
            ->setComment('wk_ws_wallet_transaction Table');
        $installer->getConnection()->createTable($table);
        
        $installer->getConnection()->addColumn(
            $setup->getTable('quote_address'),
            'wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote_address'),
            'base_wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote'),
            'wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote'),
            'base_wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'wallet_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'wallet amount'
            ]
        );
        $installer->endSetup();
    }
    /**
     * moveDirToMediaDir move directories to media
     * @return void
     */
    private function moveDirToMediaDir()
    {
        try {
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');
            $filesystem = $objManager->get('Magento\Framework\Filesystem');
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $smpleFilePath = $filesystem->getDirectoryRead($type)
                ->getAbsolutePath().'walletsystem/';
            
            $modulePath = $reader->getModuleDir('', 'Webkul_Walletsystem');
            $mediaFile = $modulePath.'/view/frontend/web/images/wallet.png';
            if (!file_exists($smpleFilePath)) {
                mkdir($smpleFilePath, 0777, true);
            }

            $filePath = $smpleFilePath.'wallet.png';
            if (!file_exists($filePath)) {
                if (file_exists($mediaFile)) {
                    copy($mediaFile, $filePath);
                }
            }
        } catch (\Exception $e) {
            /*error*/
        }
    }
}
