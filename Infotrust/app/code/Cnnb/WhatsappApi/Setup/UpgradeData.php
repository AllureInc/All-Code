<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * UpgradeData class
 * For adding customer custom attribute
 */

namespace Cnnb\WhatsappApi\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var customerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepositoryInterface;

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        LoggerInterface $logger
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->_logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            try {
                $attribute = $this->attributeRepositoryInterface->get($customerEntity, 'phone_number_verified');
                if ($attribute) {
                    $this->_logger->info('Attribute Should not be created');
                } else {
                    $this->_logger->info('Attribute Should be created');
                    $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'phone_number_verified', [
                        'group' => 'General',
                        'type' => 'int',
                        'label' => 'Phone number verified',
                        'input' => 'select',
                        'required' => false,
                        'visible' => true,
                        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                        'user_defined' => true,
                        'sort_order' => 1000,
                        'is_used_in_grid' => 1,
                        'is_visible_in_grid' => 1,
                        'is_filterable_in_grid' => 1,
                        'is_searchable_in_grid' => 1,
                        'position' => 1000,
                        'default' => 0,
                        'system' => 0,
                    ]);

                    $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'phone_number_verified')
                    ->addData(['used_in_forms' => [
                            'adminhtml_customer'
                        ]
                    ]);
                    $attribute->save();
                }
            } catch (Exception $e) {
                $this->_logger->info(' ## Upgrade Data: '.$e->getMessage());
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            try {
                $this->_logger->info('Attribute Should be created');
                $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'is_allowed_digit_update', [
                    'group' => 'General',
                    'type' => 'int',
                    'label' => 'Is allowed digits changed',
                    'input' => 'select',
                    'required' => false,
                    'visible' => true,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'user_defined' => true,
                    'sort_order' => 1000,
                    'is_used_in_grid' => 1,
                    'is_visible_in_grid' => 1,
                    'is_filterable_in_grid' => 1,
                    'is_searchable_in_grid' => 1,
                    'position' => 1000,
                    'default' => 0,
                    'system' => 0,
                ]);

                $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'is_allowed_digit_update')
                ->addData(['used_in_forms' => [
                        'adminhtml_customer'
                    ]
                ]);
                $attribute->save();
            } catch (Exception $e) {
                $this->_logger->info(' ## Upgrade Data: '.$e->getMessage());
            }
        }
    }
}
