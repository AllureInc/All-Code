<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer;

/**
 * Class IsDefault
 * @package Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer
 */
class IsDefault extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getYesNo() as $value => $label) {
                $this->addOption($value, addslashes($label));
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
    private function _getYesNo()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
