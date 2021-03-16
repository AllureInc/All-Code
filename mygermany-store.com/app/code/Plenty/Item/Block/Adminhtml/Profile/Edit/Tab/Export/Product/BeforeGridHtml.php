<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product;

use Magento\Backend\Block\Template;

/**
 * Class BeforeGridHtml
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product
 */
class BeforeGridHtml extends Template
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddToExportBtn()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => $this->escapeHtmlAttr(__('Add Products To Export')),
                'class' => 'action-secondary product-chooser-btn',
                'id' => 'product-chooser-btn'
            ]
        );
        return $button->toHtml();
    }
}
