<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Order\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer
 */
class Plenty extends Select
{
    /**
     * @var ConfigSource
     */
    private $_configSourceFactory;

    /**
     * @var array
     */
    private $_optionConfigs;

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
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            /** @var ConfigSource $item */
            foreach ($this->_getPaymentMethods() as $item) {
                $this->addOption($item->getData('id'), addslashes($item->getData('name')));
            }
        }
        return parent::_toHtml();
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
     * @return array
     */
    private function _getPaymentMethods()
    {
        if (null === $this->_optionConfigs) {
            $this->_optionConfigs = $this->_configSourceFactory->getPaymentMethods();
        }

        return $this->_optionConfigs;
    }
}
