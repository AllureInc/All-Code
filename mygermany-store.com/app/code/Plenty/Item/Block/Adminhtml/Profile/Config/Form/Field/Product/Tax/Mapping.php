<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapping
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Tax
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var
     */
    protected $_isDefaultStoreRenderer;

    /**
     * @var
     */
    protected $_mageGroupRender;

    /**
     * @var
     */
    protected $_plentyTaxGroupRender;

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
            'mage_store_tax',
            ['label' => __('Magento Tax ID'), 'renderer' => $this->_getMageGroupRender()]
        );

        $this->addColumn(
            'plenty_store_tax',
            ['label' => __('PlentyMarkets Tax ID'), 'renderer' => $this->_getPlentyGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Tax Mapping');
    }


    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Magento
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMageGroupRender()
    {
        if (!$this->_mageGroupRender) {
            $this->_mageGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageGroupRender->setClass('mage_store_tax_select');
        }
        return $this->_mageGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Plenty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getPlentyGroupRender()
    {
        if (!$this->_plentyTaxGroupRender) {
            $this->_plentyTaxGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyTaxGroupRender->setClass('plenty_store_tax_select');
        }
        return $this->_plentyTaxGroupRender;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageGroupRender()->calcOptionHash($row->getData('mage_store_tax'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyGroupRender()->calcOptionHash($row->getData('plenty_store_tax'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}
