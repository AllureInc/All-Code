<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer;

/**
 * Class IsCreatePayment
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer
 */
class IsCreatePayment extends \Magento\Framework\View\Element\Html\Select
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
