<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Widget\Item\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Plenty\Item\Api\Data\Import\ItemInterface;

/**
 * Class Sku
 * @package Plenty\Item\Block\Adminhtml\Widget\Item\Grid\Renderer
 */
class Sku extends AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $sku = $row->getData($this->getColumn()->getIndex());
        if ($productId = $row->getData(ItemInterface::EXTERNAL_ID)) {

        }

        if ($productId) {
            $html = '<a href="' . $this->_getProductUrl($productId) . '" target="_blank">' . $this->escapeHtml($sku) . '</a>';
        } else {
            $html = '<pre>'.$sku.'</pre>';
        }

        return $html;
    }

    /**
     * @param $productId
     * @return string
     */
    private function _getProductUrl($productId)
    {
        return $this->getUrl('*/catalog_product/edit', ['id' => $productId]);
    }
}
