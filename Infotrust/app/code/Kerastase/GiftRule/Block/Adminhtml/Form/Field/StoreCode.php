<?php
/**
 * @category  Cnnb
 * @package   Kerastase_GiftRule
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Kerastase\GiftRule\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Kerastase\GiftRule\Block\Adminhtml\Form\Field\StoreCodeColumn;
use Kerastase\GiftRule\Block\Adminhtml\Form\Field\SourceCodeColumn;

/**
 * StoreCode | Admin Block Class
 */
class StoreCode extends AbstractFieldArray
{
    /**
     * @var storeRenderer
     */
    private $storeCodeRenderer;

    /**
     * @var BannerColumn
     */
    private $sourceCodeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {

        $this->addColumn('kerastase_store_code', [
            'label' => __('Store View'),
            'class' => 'validate-select',
            'renderer' => $this->getStoreCodeRenderer()
        ]);
        $this->addColumn('kerastase_source_code', [
            'label' => __('MSI Stock Source'),
            'class' => 'validate-select',
            'renderer' => $this->getSourceCodeRenderer()
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
    private function getStoreCodeRenderer()
    {
        if (!$this->storeCodeRenderer) {
            $this->storeCodeRenderer = $this->getLayout()->createBlock(
                StoreCodeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->storeCodeRenderer;
    }

    /**
     * @return ProductColumn
     * @throws LocalizedException
     */
    private function getSourceCodeRenderer()
    {
        if (!$this->sourceCodeRenderer) {
            $this->sourceCodeRenderer = $this->getLayout()->createBlock(
                SourceCodeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sourceCodeRenderer;
    }
}
