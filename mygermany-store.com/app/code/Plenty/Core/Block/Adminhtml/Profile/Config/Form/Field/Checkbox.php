<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field;

/**
 * Class Checkbox
 * @package Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field
 */
class Checkbox extends \Magento\Framework\View\Element\AbstractBlock
{
    protected function _toHtml()
    {
        $elId = $this->getInputId();
        $elName = $this->getInputName();
        $colName = $this->getColumnName();
        $column = $this->getColumn();

        return '<input type="checkbox" value="1" id="' . $elId . '"' .
            ' name="' . $elName . '" <%- ' . $colName . ' %> ' .
            ($column['size'] ? 'size="' . $column['size'] . '"' : '') .
            ' class="' .
            (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
            (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }
}
