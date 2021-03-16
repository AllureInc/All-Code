<?php 
namespace Mangoit\FskRestricted\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

class UpgradeSchema implements UpgradeSchemaInterface
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


    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $this->setup2;

        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
             // CREATE ATTRIBUTE SET 
            $categorySetup = $this->categorySetupFactory->create(['setup' => $this->setup2]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

            // /*************** New Code Start ***************/
            // $attribute_group = 'Default'; 
            // /*************** New Code End ***************/
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup2]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'restricted_countries',
                [
                    'Group' => 'General',
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Restricted Countries',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => 'simple,configurable'
                ]
            );
            // /*************** New Code Start ***************/
            // $this->eavSetupFactory->addAttributeToSet(\Magento\Catalog\Model\Product::ENTITY,$attributeSetId,$attribute_group,'restricted_countries',null);
            // /*************** New Code End ***************/
            $installer->endSetup();

        }
    }
}
