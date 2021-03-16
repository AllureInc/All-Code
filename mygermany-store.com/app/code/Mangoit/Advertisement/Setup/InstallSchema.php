<?php

namespace Mangoit\Advertisement\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('mis_admin_ads')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('mis_admin_ads')
			)
				->addColumn(
					'id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'ID'
				)
				->addColumn(
					'seller_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					255,
					['nullable => false', 'default' => '0'],
					'Admin'
				)
				->addColumn(
					'ad_type',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['nullable => false', 'default' => '0'],
					'0 External, 1 Internal'
				)
				->addColumn(
					'content_type',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['nullable => false', 'default' => '1'],
					'1 image, 2 product, 3 category, 4 html1 image, 2 product, 3 category, 4 html'
				)
				->addColumn(
					'category_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false' , 'default' => 'null'],
					'category_id'
				)
				->addColumn(
					'product_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false', 'default' => 'null'],
					'product_id'
				)
				->addColumn(
					'image_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					['nullable => true'],
					'image_name'
				)
				->addColumn(
					'title',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					['nullable => true'],
					'title'
				)
				->addColumn(
					'url',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					['nullable => true'],
					'url'
				)
				->addColumn(
					'added_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					['nullable => true'],
					'added_by'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Post Table');
			$installer->getConnection()->createTable($table);
		}
		$installer->endSetup();
	}
}