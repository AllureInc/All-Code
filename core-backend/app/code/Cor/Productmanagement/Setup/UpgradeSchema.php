<?php 
/**
 * Module: Cor_Productmanagement
 * Setup Upgrade Data Script.
 * UpgradeSchema for add new table cor_purchase_order.
 */
namespace Cor\Productmanagement\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Class constructor
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory, 
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        ModuleDataSetupInterface $eavAttributeSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory; 
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavAttributeSetup = $eavAttributeSetup;
    }
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void cor_purchase_order
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table_name = 'cor_purchase_order';
            $table = $installer->getConnection()->newTable($installer->getTable($table_name))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'id'
                    )
                ->addColumn(
                    'event_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Event Id'
                )
                ->addColumn(
                    'artist_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Artist Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'purchase_order',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Purchase Order'
                );           
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $categorySetup = $this->categorySetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $attributeSet = $this->attributeSetFactory->create();
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_purchase');
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cor_purchase',
                [   'attribute_set' =>  $attributeSetId,
                    'group' => 'COR Options',
                    'label' => 'Cor Purchase',
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'input' => 'textarea',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cor_artist', 'is_required', false);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cor_category', 'is_required', false);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cor_events', 'is_required', false);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_purchase');
            $table_name = 'cor_purchase_order';
            $installer->getConnection()->dropTable($installer->getTable($table_name));
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_price_info');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cor_price_info',
                [   'attribute_set' =>  $attributeSetId,
                    'group' => 'COR Options',
                    'label' => 'Cor Price Info',
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'input' => 'text',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cor_price_info', 'is_required', true);
        }
    }
}

