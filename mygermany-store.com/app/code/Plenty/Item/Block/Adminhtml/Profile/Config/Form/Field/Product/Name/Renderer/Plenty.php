<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Name\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Profile\Config\Source\Product\Attribute\Name\Plenty as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Name\Renderer
 */
class Plenty extends Select
{
    /**
     * @var ConfigSource
     */
    protected $_configSourceFactory;

    /**
     * @var array
     */
    protected $_options;

    /**
     * Plenty constructor.
     * @param Context $context
     * @param ConfigSource $configSourceFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigSource $configSourceFactory,
        array $data = [])
    {
        $this->_configSourceFactory = $configSourceFactory;
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
            foreach ($this->_configSourceFactory->toOptionArray() as $option) {
                if (!isset($option['value']) || !isset($option['label'])) {
                    continue;
                }

                $this->addOption($option['value'], addslashes($option['label']));
            }
        }
        return parent::_toHtml();
    }


}
