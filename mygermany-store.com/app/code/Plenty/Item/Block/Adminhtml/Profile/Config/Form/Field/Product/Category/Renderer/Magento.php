<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Category\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Profile\Config\Source\Category\Magento as MagentoCategory;

/**
 * Class Magento
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Category\Renderer
 */
class Magento extends Select
{
    /**
     * @var MagentoCategory
     */
    private $_categorySource;

    /**
     * Magento constructor.
     * @param Context $context
     * @param MagentoCategory $categorySource
     * @param array $data
     */
    public function __construct(
        Context $context,
        MagentoCategory $categorySource,
        array $data = [])
    {
        $this->_categorySource = $categorySource;
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
            foreach ($this->_categorySource->toOptionArray() as $option) {
                if (!isset($option['value']) || !isset($option['label'])) {
                    continue;
                }
                $this->addOption($option['value'], addslashes($option['label']));
            }
        }
        return parent::_toHtml();
    }
}
