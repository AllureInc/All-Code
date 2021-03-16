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
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\IsDiagnosePageOption;

/**
 * FindSaloonMapping | Admin Block Class
 */
class FindSaloonMapping extends AbstractFieldArray
{
    /**
     * @var SalonRenderer
     */
    private $salonRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('gtm_element_name', ['label' => __('Class/ID Name'), 'class' => 'required-entry']);
        $this->addColumn(
            'gtm_element_is_diagnose_page',
            [
                'label' => __('Diagnose Page'),
                'class' => 'validate-select',
                'renderer' => $this->getOptionRenderer()
            ]
        );
        $this->addColumn('gtm_ecommerce', [
            'label' => __('Ecommerce')
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
     * @return Options
     * @throws LocalizedException
     */
    private function getOptionRenderer()
    {
        if (!$this->salonRenderer) {
            $this->salonRenderer = $this->getLayout()->createBlock(
                IsDiagnosePageOption::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->salonRenderer;
    }
}
