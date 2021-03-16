<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax\Renderer;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select as HtmlSelect;
use Plenty\Item\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax\Renderer
 */
class Plenty extends HtmlSelect
{
    /**
     * @var ConfigSource
     */
    protected $_configSourceFactory;

    /**
     * @var array
     */
    private $_vatConfigs;

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
        if (!$this->getOptions() && $options = $this->__getPlentyVatClasses()) {
            foreach ($options as $value => $label) {
                $this->addOption($value, addslashes($label));
            }
        }
        return parent::_toHtml();
    }

    /**
     * @return mixed
     */
    private function __getPlentyVatClasses()
    {
        if (null === $this->_vatConfigs) {
            /** @var ConfigSource $vatConfig */
            foreach ($this->_configSourceFactory->getVatConfigCollection() as $vatConfig) {
                if (!isset($vatConfig['country_name'])
                    || !$vatRates = $vatConfig->getData('vat_rates')
                ) {
                    continue;
                }

                foreach ($vatRates as $vatRate) {
                    if (!isset($vatRate['id'])
                        || !isset($vatRate['name'])
                        || !isset($vatRate['vatRate'])
                    ) {
                        continue;
                    }

                    $this->_vatConfigs[$vatRate['id']] = "{$vatConfig['country_name']} ::: {$vatRate['name']} ::: {$vatRate['vatRate']}";
                }
            }
        }

        return $this->_vatConfigs;
    }
}
