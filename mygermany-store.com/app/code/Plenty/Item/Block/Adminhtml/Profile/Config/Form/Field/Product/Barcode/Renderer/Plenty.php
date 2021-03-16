<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Barcode\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Barcode\Renderer
 */
class Plenty extends Select
{
    /**
     * @var ConfigSource
     */
    private $_configSourceFactory;

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
            foreach ($this->_configSourceFactory->getBarcodeCollection() as $item) {
                $label = "{$item->getData('name')} [id: {$item->getData('entry_id')}]";
                $this->addOption($item->getData('entry_id'), addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}