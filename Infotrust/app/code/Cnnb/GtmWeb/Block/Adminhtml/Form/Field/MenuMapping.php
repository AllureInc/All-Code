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
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\MenuMappingColumn;

/**
 * MenuMapping | Admin Block Class
 */
class MenuMapping extends AbstractFieldArray
{
    /**
     * @var MenuMapRenderer
     */
    private $menuMapRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('gtm_class_name', ['label' => __('Class Name'), 'class' => 'required-entry']);
        $this->addColumn('mega_menu_level', [
            'label' => __('Mega Menu Level'),
            'renderer' => $this->getMenuMapRenderer()
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
     * @return getMenuMapRenderer
     * @throws LocalizedException
     */
    private function getMenuMapRenderer()
    {
        if (!$this->menuMapRenderer) {
            $this->menuMapRenderer = $this->getLayout()->createBlock(
                MenuMappingColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->menuMapRenderer;
    }
}
