<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Product;

use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\ImportExport\Model\Import;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;

/**
 * Class ImportSimple
 * @package Plenty\Item\Console\Command\Product
 */
class ImportSimple extends AbstractImportCommand
{
    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $attributeOptionManagementInterface;


    protected function deleteOption($attrCode, $label){
        $attributeOptionList = $this->attributeOptionManagementInterface->getItems($attrCode);
        foreach ($attributeOptionList as $attributeOptionInterface) {
            if ($attributeOptionInterface->getLabel() == $label) {
               $this->attributeOptionManagementInterface->delete($attrCode, $attributeOptionInterface->getValue());
            }
        }
    }

    protected function addAttribute($attrCode, $label){
        $optionManagement = $this->objectManager->create('Magento\Catalog\Api\ProductAttributeOptionManagementInterface');
        $option = $this->objectManager->create('Magento\Eav\Api\Data\AttributeOptionInterface');
        // $optionLabel = $this->objectManager->create('Magento\Eav\Api\Data\AttributeOptionLabelInterface');

        $option->setLabel($label);
        $option->setValue($label);
        $option->setSortOrder(0);
        $option->setIsDefault(0);

        $optionManagement->add($attrCode, $option);
    }


    protected function configure()
    {
        // bin/magento plenty_importexport:products:importsimple
        $this->setName('plenty_importexport:products:importsimple')
            ->setDescription('Import Simple Products ');

        $this->setBehavior(Import::BEHAVIOR_ADD_UPDATE);
        $this->setEntityCode('catalog_product');

        parent::configure();
    }
    /**
     * @return array
     */
    protected function getEntities()
    {
        // $this->attributeOptionManagementInterface = $this->objectManager->get("Magento\Catalog\Api\ProductAttributeOptionManagementInterface");
        // $this->addAttribute('color', 'PurpleBlue2');
        $data = [];
        for ($i = 5; $i <= 20; $i++) {
            $data[] = [
                'sku' => 'plenty-import-simple-' . $i,
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'product_websites' => 'base',
                'name' => 'Test Product plenty ' . $i,
                'price' => '14.0000',
                // 'ean' => "1234",
                // 'color' => "PurpleBlue2"
                //'visibility' => 'Catalog, Search',
                //'tax_class_name' => 'Taxable Goods',
                //'product_online' => '1',
                //'weight' => '1.0000',
                //'short_description' => NULL,
                //'description' => '',
            ];
        }
        return $data;
    }
}