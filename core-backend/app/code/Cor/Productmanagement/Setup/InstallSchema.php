<?php
/**
 * Module: Cor_Productmanagement
 * Setup Install Script.
 * InstallSchema for installing product attributes cor_artist, cor_event and cor_category.
 */
namespace Cor\Productmanagement\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{ 
    private $eavSetupFactory;
    private $attributeSetFactory;
    private $categorySetupFactory;

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

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavAttributeSetup]);
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->eavAttributeSetup]);
        $attributeSet = $this->attributeSetFactory->create();

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_artist');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_category');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cor_event');

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cor_artist',
            [   'attribute_set' =>  $attributeSetId,
                'group' => 'COR Options',
                'label' => 'Artist',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'class' => '',
                'source' => 'Cor\Productmanagement\Model\Attribute\Source\Artist',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
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

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cor_category',
            [   'attribute_set' =>  $attributeSetId,
                'group' => 'COR Options',
                'label' => 'Artist Category',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'class' => '',
                'source' => 'Cor\Productmanagement\Model\Attribute\Source\Artistcategory',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
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

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cor_events',
            [   'attribute_set' =>  $attributeSetId,
                'group' => 'COR Options',
                'label' => 'Events',
                'type' => 'text',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'input' => 'multiselect',
                'class' => '',
                'source' => 'Cor\Productmanagement\Model\Attribute\Source\Events',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
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
}
