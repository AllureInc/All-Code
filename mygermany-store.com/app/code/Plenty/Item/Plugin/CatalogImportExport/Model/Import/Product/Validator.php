<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Plugin\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator as ProductImportValidator;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Class Validator
 * @package Plenty\Item\Plugin\CatalogImportExport\Model\Import\Product
 */
class Validator
{
    /**
     * @var array
     */
    private $_messages = [];

    /**
     * @param ValidatorInterface $subject
     * @param $value
     * @return array
     */
    public function beforeIsValid(ValidatorInterface $subject, $value)
    {
        $this->_clearMessages();
        return [$value];
    }

    /**
     * @param ValidatorInterface $subject
     * @param \Closure $proceed
     * @param $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return mixed
     */
    public function aroundIsAttributeValid(
        ValidatorInterface $subject,
        \Closure $proceed,
        $attrCode,
        array $attrParams,
        array $rowData
    ) {
        $result = $proceed($attrCode, $attrParams, $rowData);
        if ($result === false) {
            $messages = (array) $subject->getMessages();
            switch (end($messages)) {
                case RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE:
                    $this->_addMessages(sprintf(
                        '[SKU %s] %s value for attribute "%s" expected. Your input: "%s"',
                        $rowData['sku'],
                        ucfirst($attrParams['type']),
                        $attrCode,
                        $rowData[$attrCode]
                    ));
                    break;
            }
        }
        return $result;
    }

    /**
     * @param ValidatorInterface $subject
     * @param $result
     * @return array
     */
    public function afterGetMessages(ValidatorInterface $subject, $result)
    {
        return array_merge_recursive($result, $this->_getMessages());
    }

    /**
     * @return $this
     */
    private function _clearMessages()
    {
        $this->_messages = [];
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    private function _addMessages($message)
    {
        $this->_messages[] = $message;
        return $this;
    }
    /**
     * @return string[]
     */
    private function _getMessages()
    {
        return $this->_messages;
    }
}
