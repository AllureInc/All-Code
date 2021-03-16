<?php
 
namespace Cor\Customizations\Setup;
 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if ($context->getVersion() && version_compare($context->getVersion(), '0.1.2') < 0) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cor_slider_sort_order',
                [
                        'type' => 'varchar',
                        'label' => 'Cor Slider Sort Order',
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
 
        $setup->endSetup();
    }
}
