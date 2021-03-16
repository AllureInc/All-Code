<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Shipping\Renderer;

use Magento\Shipping\Model\Config\Source\Allmethods;

/**
 * Class Magento
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Shipping\Renderer
 */
class Magento extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Allmethods
     */
    private $_shippingConfigFactory;

    /**
     * @var array
     */
    private $_shippingMethods;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Allmethods $shippingMethods
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Allmethods $shippingMethods,
        array $data = [])
    {
        $this->_shippingConfigFactory = $shippingMethods;
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
            foreach ($this->_getShippingMethods() as $item) {
                if (!isset($item['value']) || !isset($item['label'])) {
                    continue;
                }

                $this->addOption($item['value'], addslashes($item['label']));
            }
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function _getShippingMethods()
    {
        if (null === $this->_shippingMethods) {
            $this->_shippingMethods = $this->_shippingConfigFactory->toOptionArray();
        }

        return $this->_shippingMethods;
    }
}
