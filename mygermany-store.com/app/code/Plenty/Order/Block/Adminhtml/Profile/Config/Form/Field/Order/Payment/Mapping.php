<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapping
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Payment
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var Renderer\Magento
     */
    protected $_magePaymentGroupRender;

    /**
     * @var Renderer\Plenty
     */
    protected $_plentyPaymentGroupRender;

    /**
     * @var
     */
    protected $_isCreatePayment;

    /**
     * Mapping constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'mage_payment',
            ['label' => __('Magento Payment'), 'renderer' => $this->_getMagePaymentGroupRender()]
        );
        $this->addColumn(
            'plenty_payment',
            ['label' => __('PlentyMarkets Payment'), 'renderer' => $this->_getPlentyPaymentGroupRender()]
        );

        $this->addColumn(
            'is_create_payment',
            ['label' => __('Create Payment'), 'renderer' => $this->_getIsCreatePaymentGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Payment Mapping');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMagePaymentGroupRender()->calcOptionHash($row->getData('mage_payment'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyPaymentGroupRender()->calcOptionHash($row->getData('plenty_payment'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getIsCreatePaymentGroupRender()->calcOptionHash($row->getData('is_create_payment'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Magento
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getMagePaymentGroupRender()
    {
        if (!$this->_magePaymentGroupRender) {
            $this->_magePaymentGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magePaymentGroupRender->setClass('mage_payment_select');
        }
        return $this->_magePaymentGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Plenty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getPlentyPaymentGroupRender()
    {
        if (!$this->_plentyPaymentGroupRender) {
            $this->_plentyPaymentGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyPaymentGroupRender->setClass('plenty_payment_select');
        }
        return $this->_plentyPaymentGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\IsCreatePayment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getIsCreatePaymentGroupRender()
    {
        if (!$this->_isCreatePayment) {
            $this->_isCreatePayment = $this->getLayout()->createBlock(
                Renderer\IsCreatePayment::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_isCreatePayment->setClass('is_create_payment');
        }
        return $this->_isCreatePayment;
    }
}
