<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Price\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Model\Config\Source as ConfigSource;

/**
 * Class Plenty
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Price\Renderer
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
            if (!$defaultLang = $this->_configSourceFactory->getDefaultLangConfig()) {
                $defaultLang = 'en';
            }
            $this->_optionConfigs = [];
            /** @var ConfigSource $vatConfig */
            foreach ($this->_configSourceFactory->getSalesPriceCollection() as $item) {
                if (!$entries = $item->getData('names')) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if (!isset($entry['salesPriceId'])
                        || !isset($entry['lang'])
                        || !isset($entry['nameInternal'])
                        || ($entry['lang'] !== $defaultLang)
                    ) {
                        continue;
                    }

                    $this->_optionConfigs[$entry['salesPriceId']] = "{$entry['nameInternal']} [id: {$entry['salesPriceId']}]";
                }
            }
        }

        return $this->_optionConfigs;
    }
}
