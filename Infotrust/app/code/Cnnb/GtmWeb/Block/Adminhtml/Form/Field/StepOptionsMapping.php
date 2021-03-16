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
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\StepOptionColumn;

/**
 * Diagnosis | Admin Block Class
 */
class StepOptionsMapping extends AbstractFieldArray
{
    /**
     * @var StepRenderer
     */
    private $stepRenderer;
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('step_name', [
            'label' => __('Diagnostic Step'),
            'class' => 'validate-select',
            'renderer' => $this->getStepRenderer()
        ]);
        $this->addColumn('step_array_text', ['label' => __('Array Label'), 'class' => 'required-entry']);
        $this->addColumn('step_option_class_id', ['label' => __('Class/IDs'), 'class' => 'required-entry']);
        $this->addColumn('step_option_value', ['label' => __('Value'), 'class' => 'required-entry']);
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
     * @return getStepRenderer
     * @throws LocalizedException
     */
    private function getStepRenderer()
    {
        if (!$this->stepRenderer) {
            $this->stepRenderer = $this->getLayout()->createBlock(
                StepOptionColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->stepRenderer;
    }
}
