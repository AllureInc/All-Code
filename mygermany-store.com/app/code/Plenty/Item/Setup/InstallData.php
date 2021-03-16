<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Class InstallData
 * @package Plenty\Item\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * Eav setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create();

        $attributes = [
            'plenty_item_id' => 'Plenty Item Id',
            'plenty_variation_id' => 'Plenty Variation Id'
        ];

        foreach ($attributes as $attributeCode => $attributeLabel) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode,
                [
                    'group'                     => 'General',
                    'type'                      => 'varchar',
                    'label'                     => $attributeLabel,
                    'input'                     => 'text',
                    'source'                    => '',
                    'frontend'                  => '',
                    'backend'                   => '',
                    'required'                  => false,
                    'default'                   => null,
                    'sort_order'                => 50,
                    'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_used_in_grid'           => true,
                    'is_visible_in_grid'        => true,
                    'is_filterable_in_grid'     => true,
                    'visible'                   => true,
                    'is_html_allowed_on_front'  => true,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'user_defined'              => true,
                ]
            );
        }
    }
}