<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleReviewSnippet
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 * 
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Cnnb\GoogleReviewSnippet\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Cnnb\GoogleReviewSnippet\Block\Adminhtml\Form\Field\AttributeColumn;

/**
 * Attributes | Admin Block Class
 */
class Attributes extends AbstractFieldArray
{
    /**
     * @var TaxColumn
     */
    private $attributeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('product_attribute', ['label' => __('Attribute Name'), 'class' => 'required-entry']);
        $this->addColumn('magento_attribute', [
            'label' => __('Magento Attribute'),
            'renderer' => $this->getAttributeRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return AttributeColumn
     * @throws LocalizedException
     */
    private function getAttributeRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                AttributeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributeRenderer;
    }
}
