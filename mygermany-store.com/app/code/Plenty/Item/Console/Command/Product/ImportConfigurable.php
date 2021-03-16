<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Product;

use Magento\Framework\App\ObjectManagerFactory;
use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\ImportExport\Model\Import;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory;

/**
 * Class ImportConfigurable
 * @package Plenty\Item\Console\Command\Product
 */
class ImportConfigurable extends AbstractImportCommand
{

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $_attributeOptionManagementInterface;

    // php -f bin/magento plenty_importexport:products:importconfigurable
    protected function configure()
    {
        $this->setName('plenty_importexport:products:importconfigurable')
            ->setDescription('Import Configurable Products ');

        $this->setBehavior(Import::BEHAVIOR_APPEND);
        $this->setEntityCode('catalog_product');

        parent::configure();
    }


    protected function deleteOption($attrCode, $label){
        $attributeOptionList = $this->_attributeOptionManagementInterface->getItems($attrCode);
        foreach ($attributeOptionList as $attributeOptionInterface) {
            if ($attributeOptionInterface->getLabel() == $label) {
                $this->_attributeOptionManagementInterface->delete($attrCode, $attributeOptionInterface->getValue());
            }
        }
    }

    /**
     * @param $attrCode
     * @param $label
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function addAttribute($attrCode, $label)
    {
        $optionManagement = $this->objectManager->create('Magento\Catalog\Api\ProductAttributeOptionManagementInterface');
        $option = $this->objectManager->create('Magento\Eav\Api\Data\AttributeOptionInterface');
        // $optionLabel = $this->objectManager->create('Magento\Eav\Api\Data\AttributeOptionLabelInterface');

        $option->setLabel($label);
        $option->setValue($label);
        $option->setSortOrder(0);
        $option->setIsDefault(0);

        $optionManagement->add($attrCode, $option);
    }

    /**
     * @return array
     */
    protected function getEntities()
    {
        /*
        $this->_attributeOptionManagementInterface = $this->objectManager->get("Magento\Catalog\Api\ProductAttributeOptionManagementInterface");
        $this->addAttribute('color', 'Blue');
        $this->addAttribute('color', 'Red');
        $this->addAttribute('size', 'Small');
        $this->addAttribute('size', 'Medium'); */

        $products = [];
        $products [] = array(
            'sku' => "SIMPLE-Black-S-1",
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            // 'product_websites' => 'base',
            'name' => 'Simple Product Blue,Size Small 1',
            'price' => '1.0000',
            'color' => 'Black',
            'size' => 'S'
        );
        $products [] = array(
            'sku' => "SIMPLE-RED-M-1",
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            // 'product_websites' => 'base',
            'name' => 'Simple Product Red,Size Middle 1',
            'price' => '1.0000',
            'color' => 'Red',
            'size' => 'M'
        );

        $products [] = array(
            'sku' => 'CONFIG-Product-1',
            'attribute_set_code' => 'Default',
            'product_type' => 'configurable',
            // 'product_websites' => 'base',
            'name' => 'Test Product Configurable 1',
            'price' => '10.000',
            'configurable_variation_labels' => 'size', // Color
            'configurable_variations' => array(
                array(
                    'sku' => 'SIMPLE-Black-S-1',
                    'color' => 'Black',
                    'size' => 'S'
                ),
                array(
                    'sku' => 'SIMPLE-RED-M-1',
                    'color' => 'Red',
                    'size' => 'M'
                ),
            )
        );

        return $products;
    }



    /**
     * Get attribute by code.
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }

    /**
     * Find or create a matching attribute option
     *
     * @param string $attributeCode Attribute the option should exist in
     * @param string $label Label to find or add
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrGetId($attributeCode, $label)
    {
        if (strlen($label) < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        // Does it already exist?
        $optionId = $this->getOptionId($attributeCode, $label);

        if (!$optionId) {
            // If no, add it.

            /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
            $optionLabel = $this->optionLabelFactory->create();
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);

            $option = $this->optionFactory->create();
            $option->setLabel($optionLabel);
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);

            $this->attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $this->getAttribute($attributeCode)->getAttributeId(),
                $option
            );

            // Get the inserted ID. Should be returned from the installer, but it isn't.
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }

        return $optionId;
    }

    /**
     * Find the ID of an option matching $label, if any.
     *
     * @param string $attributeCode Attribute code
     * @param string $label Label to find
     * @param bool $force If true, will fetch the options even if they're already cached.
     * @return int|false
     */
    public function getOptionId($attributeCode, $label, $force = false)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);

        // Build option array if necessary
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];

            // We have to generate a new sourceModel instance each time through to prevent it from
            // referencing its _options cache. No other way to get it to pick up newly-added values.

            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }

        // Return option ID if exists
        if (isset($this->attributeValues[ $attribute->getAttributeId() ][ $label ])) {
            return $this->attributeValues[ $attribute->getAttributeId() ][ $label ];
        }

        // Return false if does not exist
        return false;
    }
}