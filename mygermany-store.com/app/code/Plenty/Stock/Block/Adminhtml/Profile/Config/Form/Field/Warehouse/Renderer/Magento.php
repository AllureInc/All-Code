<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse\Renderer;

use Magento\Framework\View\Element\Html\Select as HtmlSelect;
use Magento\Framework\View\Element\Context;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Plenty\Item\Profile\Config\Source\Product\Attribute\Price as PriceAttribute;

/**
 * Class Magento
 * @package Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse\Renderer
 */
class Magento extends HtmlSelect
{
    /**
     * @var PriceAttribute
     */
    private $_priceAttributeSource;

    /**
     * Magento constructor.
     * @param Context $context
     * @param PriceAttribute $priceAttributeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        PriceAttribute $priceAttributeSource,
        array $data = [])
    {
        $this->_priceAttributeSource = $priceAttributeSource;
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
            /** @var ProductAttributeInterface $priceAttribute */
            foreach ($this->_priceAttributeSource->getPriceAttributeCollection() as $priceAttribute) {
                if (!$priceAttribute->getAttributeCode() || !$priceAttribute->getDefaultFrontendLabel()) {
                    continue;
                }
                $this->addOption($priceAttribute->getAttributeCode(), addslashes($priceAttribute->getDefaultFrontendLabel()));
            }
        }
        return parent::_toHtml();
    }
}
