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

/**
 * PdpMapping | Admin Block Class
 */
class PdpTabMapping extends AbstractFieldArray
{
    /**
     * @var PdpTabRenderer
     */
    private $pdpTabRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('gtm_element_name', ['label' => __('Class/ID Name'), 'class' => 'required-entry']);
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
}
