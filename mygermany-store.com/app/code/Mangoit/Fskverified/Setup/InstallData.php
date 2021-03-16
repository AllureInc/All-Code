<?php
/* app/code/Atwix/TestAttribute/Setup/InstallData.php */

namespace Mangoit\Fskverified\Setup;

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
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    
    private $attributeSetFactory;
    private $categorySetupFactory;
    private $customerSetupFactory;
    protected $eavConfig;
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
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
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
         
        /**
         * Add attributes to the eav/attribute
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]); // for customer
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $customerAttributeSetId = $customerEntity->getDefaultAttributeSetId();
        $customerAttributeSet = $this->attributeSetFactory->create();
        $customerAttributeGroupId = $customerAttributeSet->getDefaultGroupId($customerAttributeSetId);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'fsk_product_type');
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $attributeSet = $this->attributeSetFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);


        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'fsk_product_type',/* Custom Attribute Code */
            [
                'group'=> 'General',
                'type' => 'int',/* Data type in which formate your value save in database*/
                'backend' => '',
                'frontend' => '',
                'label' => 'FSK compulsory', /* lablel of your attribute*/
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                                /* Source of your select type custom attribute options*/
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                                    /*Scope of your attribute */
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'value'=> false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );



        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 
            'fsk_customer',
            [
                'label' => 'FSK verified',
                'system' => 0,
                'input' => 'select',
                'type' => 'int',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'unique' => false,
                'value' => 0,
                'sort_order' => 4, 
                'default' => false           
            ]
        );

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 
            'fsk_doc',
            [
                'input' => 'text',
                'type' => 'text',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'label' => 'FSK Doc',
                'required' => false,
                'unique' => false,
                'sort_order' => 5,
                'visible' => false,
                'user_defined' => true,  
                'default' => false              
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'fsk_customer')
        ->addData([
            'attribute_set_id' => $customerAttributeSetId,
            'attribute_group_id' => $customerAttributeGroupId,
            'is_user_defined' => 1,
            'used_in_forms' => ['customer_account_create', 'customer_account_edit'],
        ]);

        $attribute->save();

    }
}