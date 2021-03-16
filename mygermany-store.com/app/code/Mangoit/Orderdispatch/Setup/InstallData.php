<?php
/* app/code/Atwix/TestAttribute/Setup/InstallData.php */

namespace Mangoit\Orderdispatch\Setup;

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


        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 
            'compliance_check',
            [
                'label' => 'Compliance Check',
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
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'compliance_check')
            ->addData([
            'attribute_set_id' => $customerAttributeSetId,
            'attribute_group_id' => $customerAttributeGroupId,
            'is_user_defined' => 1,
            'used_in_forms' => ['customer_account_create', 'customer_account_edit'],
        ]);

       

        $attribute->save();

    }
}
