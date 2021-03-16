<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Cnnb\GtmWeb\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\YesNoColumn;

/**
 * Diagnosis | Admin Block Class
 */
class DiagnosisMapping extends AbstractFieldArray
{
    /**
     * @var YesNoRenderer
     */
    private $yesNoRenderer;
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('gtm_step', [
            'label' => __('Diagnostic step')
        ]);
        $this->addColumn('gtm_element_name', ['label' => __('Event Name'), 'class' => 'required-entry']);
        $this->addColumn('gtm_page_virtual', [
            'label' => __('Page Virtual'),
            'renderer' => $this->getYesNoRenderer()
        ]);
        $this->addColumn('gtm_virtual_url', [
            'label' => __('Virtual Page URL')
        ]);
        $this->addColumn('gtm_virtual_title', [
            'label' => __('Virtual Page Title')
        ]);
        $this->addColumn('gtm_step_class_id', ['label' => __('Class/IDs'), 'class' => 'required-entry']);
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
     * @return getYesNoRenderer
     * @throws LocalizedException
     */
    private function getYesNoRenderer()
    {
        if (!$this->yesNoRenderer) {
            $this->yesNoRenderer = $this->getLayout()->createBlock(
                YesNoColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->yesNoRenderer;
    }
}
