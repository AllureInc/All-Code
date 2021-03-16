<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Mega Menu Options
 */
namespace Cnnb\GtmWeb\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

/**
 * MenuMappingColumn | Admin Block Class
 */
class MenuMappingColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Provides Menu Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return [
            [
                'label'=> __('Please Select'),
                'value'=> ''
            ],
            [
                'value'=> 'level_1',
                'label'=> __('Mega Menu Level :: One')
            ],
            [
                'value'=> 'level_2',
                'label'=> __('Mega Menu Level :: Two')
            ],
            [
                'value'=> 'level_3',
                'label'=> __('Mega Menu Level :: Three')
            ]
        ];
    }
}
