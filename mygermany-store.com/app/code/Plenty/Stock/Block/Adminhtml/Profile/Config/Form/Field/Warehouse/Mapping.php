<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Mapping
 * @package Plenty\Stock\Block\Adminhtml\Profile\Config\Form\Field\Warehouse
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var
     */
    protected $_mageWarehouseGroupRender;

    /**
     * @var
     */
    protected $_plentyWarehouseGroupRender;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
       Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'mage_warehouse',
            ['label' => __('Magento Warehouse ID'), 'renderer' => $this->_getMageWarehouseGroupRender()]
        );

        $this->addColumn(
            'plenty_warehouse',
            ['label' => __('PlentyMarkets Warehouse ID'), 'renderer' => $this->_getPlentyWarehouseGroupRender()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Warehouse Mapping');
    }


    /**
     * @return BlockInterface|Renderer\Magento
     * @throws LocalizedException
     */
    protected function _getMageWarehouseGroupRender()
    {
        if (!$this->_mageWarehouseGroupRender) {
            $this->_mageWarehouseGroupRender = $this->getLayout()->createBlock(
                Renderer\Magento::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mageWarehouseGroupRender->setClass('mage_warehouse_select');
        }
        return $this->_mageWarehouseGroupRender;
    }

    /**
     * @return BlockInterface|Renderer\Plenty
     * @throws LocalizedException
     */
    protected function _getPlentyWarehouseGroupRender()
    {
        if (!$this->_plentyWarehouseGroupRender) {
            $this->_plentyWarehouseGroupRender = $this->getLayout()->createBlock(
                Renderer\Plenty::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_plentyWarehouseGroupRender->setClass('plenty_warehouse_select');
        }
        return $this->_plentyWarehouseGroupRender;
    }

    /**
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMageWarehouseGroupRender()->calcOptionHash($row->getData('mage_warehouse'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPlentyWarehouseGroupRender()->calcOptionHash($row->getData('plenty_warehouse'))] = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}
