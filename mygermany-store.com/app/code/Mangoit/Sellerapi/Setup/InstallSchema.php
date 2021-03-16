<?php 
namespace Mangoit\Sellerapi\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

use Magento\Framework\Setup\InstallSchemaInterface;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallSchema implements InstallSchemaInterface
{
    private $eavSetupFactory;
    private $attributeSetFactory;
    private $categorySetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup2,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup2 = $setup2;
        $this->attributeSetFactory = $attributeSetFactory; 
        $this->categorySetupFactory = $categorySetupFactory;
    }


    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $this->setup2;

        $installer->startSetup(); 
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->setup2]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup2]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'config_options_attr',
            [
                'Group' => 'General',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Configurable Options',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'config_child_sku',
            [
                'Group' => 'General',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Configurable SKU',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        
        $installer->endSetup();

    }
}
