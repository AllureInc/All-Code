<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapping
 * @package Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store
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
    protected $_mageStoreGroupRender;

    /**
     * @var
     */
    protected $_plentyStoreGroupRender;

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
            'mage_store',
            ['label' => __('Magento Store'), 'renderer' => $this->_getMageStoreGroupRender()]
        );
        $this->addColumn(
            'plenty_store',
            ['label' => __('PlentyMarkets Store'), 'renderer' => $this->_getPlentyStoreGroupRender()]
        );
        $this->addColumn(
            'is_default',
            ['label' => __('Is Default'), 'renderer' => $this->_getIsDefaultStoreGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Store Mapping');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageStoreGroupRender()->calcOptionHash($row->getData('mage_store'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyStoreGroupRender()->calcOptionHash($row->getData('plenty_store'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getIsDefaultStoreGroupRender()->calcOptionHash($row->getData('is_default'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Magento
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getMageStoreGroupRender()
    {
        if (!$this->_mageStoreGroupRender) {
            $this->_mageStoreGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageStoreGroupRender->setClass('mage_store_select');
        }
        return $this->_mageStoreGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\Plenty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getPlentyStoreGroupRender()
    {
        if (!$this->_plentyStoreGroupRender) {
            $this->_plentyStoreGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyStoreGroupRender->setClass('plenty_store_select');
        }
        return $this->_plentyStoreGroupRender;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Renderer\IsDefault
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getIsDefaultStoreGroupRender()
    {
        if (!$this->_isDefaultStoreRenderer) {
            $this->_isDefaultStoreRenderer = $this->getLayout()->createBlock(
                Renderer\IsDefault::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_isDefaultStoreRenderer->setClass('is_default');
        }
        return $this->_isDefaultStoreRenderer;
    }
}
