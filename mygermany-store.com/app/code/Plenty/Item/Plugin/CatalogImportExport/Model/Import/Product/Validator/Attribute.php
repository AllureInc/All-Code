<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Plugin\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Eav\Model\Config as EavConfig;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Attribute
 * @package Plenty\Item\Plugin\CatalogImportExport\Model\Import\Product\Validator
 */
class Attribute
{
    /**
     * @var EavConfig
     */
    private $_eavConfig;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private $_optionInterfaceFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $_attributeOptionManagement;

    /**
     * @var AbstractType
     */
    private $_productImportAbstractType;

    /**
     * Attribute constructor.
     * @param EavConfig $eavConfig
     * @param AttributeOptionInterfaceFactory $optionInterfaceFactory
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagement
     * @param AbstractType $productImportAbstractType
     */
    public function __construct(
        EavConfig $eavConfig,
        AttributeOptionInterfaceFactory $optionInterfaceFactory,
        ProductAttributeOptionManagementInterface $attributeOptionManagement,
        AbstractType $productImportAbstractType
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_optionInterfaceFactory = $optionInterfaceFactory;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->_productImportAbstractType = $productImportAbstractType;
    }

    /**
     * @param Validator $subject
     * @param $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return array
     * @throws LocalizedException
     */
    public function beforeIsAttributeValid(Validator $subject, $attrCode, array $attrParams, array $rowData)
    {
        if ($attrParams['type'] != "multiselect" && $attrParams['type'] != "select") {
            return [$attrCode, $attrParams, $rowData];
        }

        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attrCode);
        if (!$this->_isAttributeApplicable($attribute)) {
            return [$attrCode, $attrParams, $rowData];
        }

        $values = explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData[$attrCode]);

        $values = array_filter($values, function ($optionName) use ($attrParams) {
            if (!strlen($optionName)) {
                return false;
            }

            $indexValue = strtolower($optionName);
            if (isset($attrParams['options'][$indexValue])) {
                return false;
            }

            return true;
        });

        foreach ($values as $value) {
            $indexValue = strtolower($value);
            try {
                $option = $this->_createAttributeOption($attrCode, $value);
            } catch (\Exception $e) {
                continue;
            }

            $attrParams['options'][$indexValue] = $option->getValue();
            // AbstractType::$commonAttributesCache = [];
            $this->_productImportAbstractType->__destruct();
        }

        return [$attrCode, $attrParams, $rowData];
    }

    /**
     * @param $attributeCode
     * @param $label
     * @return AttributeOptionInterface|null
     * @throws InputException
     * @throws StateException
     */
    public function _createAttributeOption($attributeCode, $label)
    {
        if (!$option = $this->_findAttributeOptionByLabel($attributeCode, $label)) {
            $option = $this->_optionInterfaceFactory->create();
            $option->setLabel($label);

            if (!$result = $this->_attributeOptionManagement->add($attributeCode, $option)) {
                throw new \Exception(__('Could not create label "%1" for attribute "$2".', $label, $attributeCode));
            }

            $this->_eavConfig->clear();
            $option = $this->_findAttributeOptionByLabel($attributeCode, $label);
        }

        if (!$option) {
            throw new \Exception(__('Could not find attribute label "%1" for attribute "$2".', $label, $attributeCode));
        }

        return $option;
    }

    /**
     * @param $attributeCode
     * @param $label
     * @return AttributeOptionInterface|null
     * @throws InputException
     * @throws StateException
     */
    private function _findAttributeOptionByLabel($attributeCode, $label)
    {
        $attributeOptionList = $this->_attributeOptionManagement->getItems($attributeCode);
        foreach ($attributeOptionList as $attributeOptionInterface) {
            if (strcmp($attributeOptionInterface->getLabel(), $label) === 0) {
                return $attributeOptionInterface;
            }
        }

        return null;
    }

    /**
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function _isAttributeApplicable(AbstractAttribute $attribute)
    {
        return $attribute->getSourceModel() instanceof AbstractSource
            || null === $attribute->getSourceModel();
    }
}
