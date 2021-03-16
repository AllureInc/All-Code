<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer;

use Magento\Payment\Model\Config;

/**
 * Class Magento
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment\Renderer
 */
class Magento extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Config
     */
    private $_paymentConfigFactory;

    /**
     * @var array
     */
    private $_paymentMethods;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Config $paymentConfig,
        array $data = [])
    {
        $this->_paymentConfigFactory = $paymentConfig;
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
            foreach ($this->_getPaymentMethods() as $code => $model) {
                if (!$label = $this->_scopeConfig->getValue('payment/'.$code.'/title')) {
                    continue;
                }
                $this->addOption($code, addslashes($label));
            }
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function _getPaymentMethods()
    {
        if (null === $this->_paymentMethods) {
            $this->_paymentMethods = $this->_paymentConfigFactory->getActiveMethods();
        }

        return $this->_paymentMethods;
    }
}
