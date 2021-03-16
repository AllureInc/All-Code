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
use Cnnb\Gtm\Block\Adminhtml\Form\Field\BannerColumn;
use Cnnb\Gtm\Block\Adminhtml\Form\Field\BannerClickColumn;

/**
 * Banner | Admin Block Class
 */
class Banner extends AbstractFieldArray
{
    /**
     * @var BannerColumn
     */
    private $bannerRenderer;

    /**
     * @var BannerColumn
     */
    private $productRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {

        $this->addColumn('gtm_event_label', [
            'label' => __('Event Label'),
            'class' => 'validate-select',
            'renderer' => $this->getBannerRenderer()
        ]);
        $this->addColumn('gtm_id', ['label' => __('ID'), 'class' => 'required-entry']);
        $this->addColumn('gtm_name', ['label' => __('Name'), 'class' => 'required-entry']);
        $this->addColumn('gtm_creative', ['label' => __('Creative'), 'class' => 'required-entry']);
        $this->addColumn('gtm_posotion', ['label' => __('Position'), 'class' => 'required-entry']);
        $this->addColumn('gtm_product_associated', [
            'label' => __('Associated Product'),
            'class' => 'validate-select',
            'renderer' => $this->getProductRenderer()
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
    private function getBannerRenderer()
    {
        if (!$this->bannerRenderer) {
            $this->bannerRenderer = $this->getLayout()->createBlock(
                BannerColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->bannerRenderer;
    }

    /**
     * @return ProductColumn
     * @throws LocalizedException
     */
    private function getProductRenderer()
    {
        if (!$this->productRenderer) {
            $this->productRenderer = $this->getLayout()->createBlock(
                BannerClickColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->productRenderer;
    }
}
