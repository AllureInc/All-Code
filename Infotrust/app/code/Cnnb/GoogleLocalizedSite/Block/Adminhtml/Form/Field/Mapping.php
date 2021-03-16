<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleLocalizedSite
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Cnnb\GoogleLocalizedSite\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Cnnb\GoogleLocalizedSite\Block\Adminhtml\Form\Field\CountryColumn;
use Cnnb\GoogleLocalizedSite\Block\Adminhtml\Form\Field\LanguageColumn;

/**
 * Banner | Admin Block Class
 */
class Mapping extends AbstractFieldArray
{
    /**
     * @var BannerColumn
     */
    private $countryRenderer;

    /**
     * @var BannerColumn
     */
    private $languageRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {

        $this->addColumn('cnnb_country', [
            'label' => __('Country'),
            'class' => 'validate-select',
            'renderer' => $this->getCountryRenderer()
        ]);
        $this->addColumn('cnnb_language', [
            'label' => __('Language'),
            'class' => 'validate-select',
            'renderer' => $this->getLanguageRenderer()
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
    private function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                CountryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }

    /**
     * @return ProductColumn
     * @throws LocalizedException
     */
    private function getLanguageRenderer()
    {
        if (!$this->languageRenderer) {
            $this->languageRenderer = $this->getLayout()->createBlock(
                LanguageColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->languageRenderer;
    }
}
