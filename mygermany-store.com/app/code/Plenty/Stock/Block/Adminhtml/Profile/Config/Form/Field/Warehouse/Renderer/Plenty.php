<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Core\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse\Renderer
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
    private $_optionConfigs;

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
            foreach ($this->__getOptionsArray() as $value => $label) {
                $this->addOption($value, addslashes($label));
            }
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function __getOptionsArray()
    {
        if (null === $this->_optionConfigs) {
            /** @var ConfigSource $vatConfig */
            foreach ($this->_configSourceFactory->getWarehouseConfigCollection() as $item) {
                if (!$item->getData('entry_id') || !$item->getData('name')
                ) {
                    continue;
                }
                $this->_optionConfigs[$item->getData('entry_id')] = "{$item->getData('name')} [id: {$item->getData('entry_id')}]";
            }
        }

        return $this->_optionConfigs;
    }
}
