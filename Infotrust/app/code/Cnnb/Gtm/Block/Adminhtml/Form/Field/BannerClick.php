<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Cnnb\Gtm\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Cnnb\Gtm\Block\Adminhtml\Form\Field\BannerClickColumn;

/**
 * BannerClick | Admin Block Class
 */
class BannerClick extends AbstractFieldArray
{
    /**
     * @var BannerColumn
     */
    private $bannerClickRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {

        $this->addColumn('gtm_event_label', [
            'label' => __('Event Label'),
            'class' => 'validate-select',
            'renderer' => $this->getBannerClickRenderer()
        ]);
        $this->addColumn('gtm_id', ['label' => __('ID'), 'class' => 'required-entry']);
        $this->addColumn('gtm_name', ['label' => __('Name'), 'class' => 'required-entry']);
        $this->addColumn('gtm_creative', ['label' => __('Creative'), 'class' => 'required-entry']);
        $this->addColumn('gtm_posotion', ['label' => __('Position'), 'class' => 'required-entry']);
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
    private function getBannerClickRenderer()
    {
        if (!$this->bannerClickRenderer) {
            $this->bannerClickRenderer = $this->getLayout()->createBlock(
                BannerClickColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->bannerClickRenderer;
    }
}
