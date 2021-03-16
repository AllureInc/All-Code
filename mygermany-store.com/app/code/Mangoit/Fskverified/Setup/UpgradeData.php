<?php
namespace Mangoit\Fskverified\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;


/* irrelevant */
#use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
/* irrelevant */
#use Magento\Framework\Setup\SchemaSetupInterface;
/* add this */
use Magento\Framework\Setup\UpgradeDataInterface;
/**
* 
*/
class UpgradeData implements  UpgradeDataInterface{

	private $attributeSetFactory;
    private $categorySetupFactory;
    private $customerSetupFactory;
    protected $eavConfig;
    private $eavSetupFactory;

    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory, 
    	AttributeSetFactory $attributeSetFactory,
    	CategorySetupFactory $categorySetupFactory)
    {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory; 
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        /* assign object to class global variable for use in other class methods */
    }

    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context){
        $setup->startSetup();

            if (version_compare($context->getVersion(), '1.0.1') < 0) {
	           $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
	        	$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'product_note');
	        	$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
	        	$attributeSet = $this->attributeSetFactory->create();
	        	$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
	        	$attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
	        	$eavSetup->addAttribute(
	            \Magento\Catalog\Model\Product::ENTITY,
		            'product_note',/* Custom Attribute Code */
		            [
		                'group'=> 'General',
		                'type' => 'text',/* Data type in which formate your value save in database*/
		                'backend' => '',
		                'frontend' => '',
		                'label' => 'Internal Note', /* lablel of your attribute*/
		                'input' => 'textarea',
		                'class' => '',
		                'source' => '',
		                                /* Source of your select type custom attribute options*/
		                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
		                                    /*Scope of your attribute */
		                'visible' => true,
		                'required' => false,
		                'user_defined' => false,
		                'default' => '',
		                'value'=> false,
		                'searchable' => false,
		                'filterable' => false,
		                'comparable' => false,
		                'visible_on_front' => false,
		                'used_in_product_listing' => true,
		                'unique' => false
		            ]
	            );
        }    
        $setup->endSetup();
    }
}