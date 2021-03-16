<?php
    
namespace Cor\Customizations\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory; 

class InstallData implements InstallDataInterface
{
    /**
    * Category setup factory
    *
    * @var CategorySetupFactory
    */
    private $categorySetupFactory;
    /**
    * Init
    *
    * @param CategorySetupFactory $categorySetupFactory
    */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
         $this->categorySetupFactory = $categorySetupFactory;
    }

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'cor_use_in_slider',
            [
                'type' => 'varchar',
                'label' => 'Cor use in slider',
                'input' => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required' => false,
                'group' => 'General Information',
                'default' => NULL,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false
            ]
        );
    }
}