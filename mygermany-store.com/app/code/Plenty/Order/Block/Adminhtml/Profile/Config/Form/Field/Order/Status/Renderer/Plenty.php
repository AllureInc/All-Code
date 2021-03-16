<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Order\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status\Renderer
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
            foreach ($this->_getOrderStatuses() as $item) {
                $this->addOption($item->getData('status_id'), addslashes($item->getData('default_name')));
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
    private function _getOrderStatuses()
    {
        if (null === $this->_optionConfigs) {
            $this->_optionConfigs = $this->_configSourceFactory->getOrderStatuses();
        }

        return $this->_optionConfigs;
    }
}
