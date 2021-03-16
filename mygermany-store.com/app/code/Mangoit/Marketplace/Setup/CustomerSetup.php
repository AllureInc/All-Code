<?php

namespace Mangoit\Marketplace\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

class CustomerSetup extends EavSetup {

    protected $eavConfig;
    private $customerSetupFactory;
    private $setup;
    private $attributeSetFactory;
    protected $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        Config $eavConfig,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
        ) {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        parent :: __construct($setup, $context, $cache, $attrGroupCollectionFactory);
    } 

    // public function installAttributes($customerSetup) {
    //     $this->installCustomerAttributes($customerSetup);
    // } 

    public function installCustomerAttributes() {

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'deactivated_account', [
            'input' => 'select',
            'type' => 'int',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'label' => 'Deactivate Account',
            'required' => 0,
            'unique' => 0,
            'sort_order' => 3,
            'user_defined' => 1,
        ]);
        //add attribute to attribute set
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'deactivated_account')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['customer_account_create', 'customer_account_edit'],
        ]);

        $attribute->save();



    }

    public function updateCustomerAttributes() {

        /** @var CustomerSetup $customerSetup */
            $eavSetup = $this->eavSetupFactory->create();
            $entityTypeId = 1; 
            $eavSetup->removeAttribute($entityTypeId, 'deactivated_account');
    }

    public function getEavConfig() {
        return $this->eavConfig;
    }
}