<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Productfaq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/*
 * Creating required Tables
 * */
class InstallSchema implements InstallSchemaInterface
{
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_productfaq')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'unsigned' => true,'nullable'  => false,'primary'   => true,),
            'id'
        )
            ->addColumn(
                'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, array(
                ), 'product_id'
            )
            ->addColumn(
                'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2055, array(
                ), 'title'
            )
            ->addColumn(
                'description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2055, array(
                ), 'description'
            )
            ->addColumn(
                'email_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                ), 'email_id'
            )
            ->addColumn(
                'posted_by', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2055, array(
                ), 'posted_by'
            )
            ->addColumn(
                'publish_date', \Magento\Framework\DB\Ddl\Table::TYPE_DATE, null, array(
                ), 'publish_date'
            )
            ->addColumn(
                'is_active', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, array(
                ), 'is_active'
            );
             
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('ced_like'))
            ->addColumn(
                'id',  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ), 'id'
            )
            ->addColumn(
                'question_id',  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, array(
                'nullable'  => false,
                ), 'question_id'
            )
            ->addColumn(
                'product_id',  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, array(
                'nullable'  => false,
                ), 'product_id'
            )
            ->addColumn(
                'user_ip',  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 555, array(
                'nullable'  => false,
                ), 'user_ip'
            )
            ->addColumn(
                'likes',  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10,  array(
                'nullable'  => false,
                ), 'likes'
            );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();

    }
}
