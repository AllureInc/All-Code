<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ImageGallery
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Setup;

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
        $setup->startSetup();
        $tableName = $setup->getTable('marketplace_seller_vacation');
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.2') < 0) {

            /**
         * Update table marketplace_seller_vacation
         */
            $connection->modifyColumn(
                $tableName,
                'date_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                ['nullable' => false, 'default' => '']
            );
            $connection->modifyColumn(
                $tableName,
                'date_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                ['nullable' => false, 'default' => '']
            );
            $setup->endSetup();
        }
    }
}
