<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $eavSetup= $this->_eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'wallet_credit_based_on',
                [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Product Wallet Credit Amount Based On', /* lablel of your attribute*/
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Webkul\Walletsystem\Model\Config\Source\ProductattrOptions',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'note' => 'Product Credit amount based on rules amount or on wallet credit amount',
                    'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $eavSetup= $this->_eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'wallet_cash_back');
            $eavSetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'wallet_cash_back',
                [
                    'type' => 'decimal',
                    'label' => 'Wallet Credit Amount',
                    'input' => 'price',
                    'required' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'Product Details',
                    'used_in_product_listing' => true,
                    'visible_on_front' => true,
                    'visible' => true,
                    'user_defined' => true,
                    'unique' => false,
                    'is_configurable' => false,
                    'used_for_promo_rules' => true,
                    'backend' => '',
                    'default' => 0,
                    'frontend' => '',
                    'frontend_class'=>'validate-zero-or-greater',
                    'label' =>  'Wallet Cash Back',
                    'note' => 'Product wise credit amount.',
                    'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
                ]
            );
        }
    }
}
