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
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\ProductRenderer;
use Cnnb\GtmWeb\Block\Adminhtml\Form\Field\StoreNameList;

/**
 * DiagnosticShopNowCta | Admin Block Class
 */
class DiagnosticShopNowCta extends AbstractFieldArray
{
    /**
     * @var ProductRenderer
     */
    private $productRenderer;

    /**
     * @var StoreRenderer
     */
    private $storeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('gtm_element_name', ['label' => __('Class/ID Name'), 'class' => 'required-entry']);
        $this->addColumn('gtm_element_store', ['label' => __('Store'), 'class' => 'required-entry']);
        $this->addColumn('gtm_element_product', [
            'label' => __('Product'),
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
     * ProductRenderer
     */
    private function getProductRenderer()
    {
        if (!$this->productRenderer) {
            $this->productRenderer = $this->getLayout()->createBlock(
                ProductRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->productRenderer;
    }
}
