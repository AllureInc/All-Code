<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Widget\Product\Chooser;

use Magento\Backend\Block\Template;

/**
 * Class Container
 * @package Plenty\Item\Block\Adminhtml\Widget\Product\Chooser
 */
class Container extends Template
{
    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductChooserGridJson()
    {
        $gridBlock = $this->_getGridBlock();
        return json_encode(
            [
                'html' => $gridBlock->toHtml(),
                'url' => $this->getAddToExportUrl(),
                'trigger_btn' => 'product-chooser-btn',
                'massaction_obj' => $gridBlock->getMassactionBlock()->getJsObjectName(),
                'product_export_grid_obj' => 'product_export_gridJsObject',
            ]
        );
    }

    /**
     * @return string
     */
    public function getAddToExportUrl()
    {
        return $this->getUrl('plenty_item/export_product/addProduct', ['_current' => true]);

    }

    /**
     * @return \Plenty\Item\Block\Adminhtml\Widget\Product\Chooser
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getGridBlock()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        /** @var \Plenty\Item\Block\Adminhtml\Widget\Product\Chooser $chooser */
        $chooser = $this->getLayout()->createBlock(\Plenty\Item\Block\Adminhtml\Widget\Product\Chooser::class)
            ->setUseMassaction(true)
            ->setSelectedProducts(explode(',', $selected));

        return $chooser;
    }
}