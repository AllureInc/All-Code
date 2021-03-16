<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapping
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var Renderer\Magento
     */
    protected $_mageStatusGroupRender;

    /**
     * @var Renderer\Plenty
     */
    protected $_plentyStatusGroupRender;

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
            'mage_status',
            ['label' => __('Magento Status'), 'renderer' => $this->_getMageStatusGroupRender()]
        );
        $this->addColumn(
            'plenty_status',
            ['label' => __('PlentyMarkets Status'), 'renderer' => $this->_getPlentyStatusGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Status Mapping');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageStatusGroupRender()->calcOptionHash($row->getData('mage_status'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyStatusGroupRender()->calcOptionHash($row->getData('plenty_status'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Magento
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getMageStatusGroupRender()
    {
        if (!$this->_mageStatusGroupRender) {
            $this->_mageStatusGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageStatusGroupRender->setClass('mage_status_select');
        }
        return $this->_mageStatusGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Plenty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getPlentyStatusGroupRender()
    {
        if (!$this->_plentyStatusGroupRender) {
            $this->_plentyStatusGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyStatusGroupRender->setClass('plenty_status_select');
        }
        return $this->_plentyStatusGroupRender;
    }
}
