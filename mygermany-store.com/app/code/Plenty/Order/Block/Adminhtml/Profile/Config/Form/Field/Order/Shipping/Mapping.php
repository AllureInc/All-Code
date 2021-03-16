<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Shipping;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapping
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Shipping
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var Renderer\Magento
     */
    protected $_mageShippingGroupRender;

    /**
     * @var Renderer\Plenty
     */
    protected $_plentyShippingGroupRender;

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
            'mage_shipping',
            ['label' => __('Magento Shipping'), 'renderer' => $this->_getMageShippingGroupRender()]
        );
        $this->addColumn(
            'plenty_shipping',
            ['label' => __('PlentyMarkets Shipping'), 'renderer' => $this->_getPlentyShippingGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Shipping Mapping');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageShippingGroupRender()->calcOptionHash($row->getData('mage_shipping'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyShippingGroupRender()->calcOptionHash($row->getData('plenty_shipping'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Magento
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getMageShippingGroupRender()
    {
        if (!$this->_mageShippingGroupRender) {
            $this->_mageShippingGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageShippingGroupRender->setClass('mage_shipping_select');
        }
        return $this->_mageShippingGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Plenty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getPlentyShippingGroupRender()
    {
        if (!$this->_plentyShippingGroupRender) {
            $this->_plentyShippingGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyShippingGroupRender->setClass('plenty_shipping_select');
        }
        return $this->_plentyShippingGroupRender;
    }
}
