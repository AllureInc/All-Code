<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer;

use Magento\Framework\View\Element\Context;
use Plenty\Core\Model\Profile\Config\Source\Language\Codes;

/**
 * Class Plenty
 * @package Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer
 */
class Plenty extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var array
     */
    private $_stores;

    /**
     * @var Codes
     */
    private $_languageCodeOptionSource;

    /**
     * Plenty constructor.
     * @param Context $context
     * @param Codes $languageCodeOptionSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Codes $languageCodeOptionSource,
        array $data = [])
    {
        $this->_languageCodeOptionSource = $languageCodeOptionSource;
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
            foreach ($this->_getLanguageCodes() as $code => $label) {
                $this->addOption($code, addslashes($label));
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
    private function _getLanguageCodes()
    {
        if (null === $this->_stores) {
            $this->_stores = [];
            foreach ($this->_languageCodeOptionSource->toOptionArray() as $languageCode) {
                if (!isset($languageCode['value']) && !isset($languageCode['label'])) {
                    continue;
                }
                $this->_stores[$languageCode['value']] = "{$languageCode['value']} {$languageCode['label']}";
            }
        }

        return $this->_stores;
    }
}
