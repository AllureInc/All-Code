<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax\Renderer;

/**
 * Class Magento
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax\Renderer
 */
class Magento extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Product
     */
    private $_productTaxClassesSource;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassesSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassesSource,
        array $data = [])
    {
        $this->_productTaxClassesSource = $productTaxClassesSource;
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
            foreach ($this->_productTaxClassesSource->getAllOptions() as $option) {
                if (!isset($option['value']) || !isset($option['label'])) {
                    continue;
                }
                $this->addOption($option['value'], addslashes($option['label'] . ' [id: '. $option['value'] . ']'));
            }
        }
        return parent::_toHtml();
    }
}
