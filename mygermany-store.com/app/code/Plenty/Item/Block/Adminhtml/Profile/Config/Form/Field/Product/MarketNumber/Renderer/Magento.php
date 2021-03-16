<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\MarketNumber\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Plenty\Item\Profile\Config\Source\Product\Attribute;

/**
 * Class Magento
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\MarketNumber\Renderer
 */
class Magento extends Select
{
    /**
     * @var Attribute
     */
    private $_attributeSource;

    /**
     * Magento constructor.
     * @param Context $context
     * @param Attribute $attributeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Attribute $attributeSource,
        array $data = [])
    {
        $this->_attributeSource = $attributeSource;
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            /** @var ProductAttributeInterface $attribute */
            foreach ($this->_attributeSource->getAttributeCollection('text') as $attribute) {
                $this->addOption($attribute->getAttributeCode(), addslashes($attribute->getDefaultFrontendLabel()));
            }
        }
        return parent::_toHtml();
    }
}
