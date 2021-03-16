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
 * YesNoColumn | Admin Block Class
 */
class YesNoColumn extends Select
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
     * Provides yes no
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return [
            [
                'value'=> __('yes'),
                'label'=> __('Yes')
            ],
            [
                'value'=> __('no'),
                'label'=> __('No')
            ]
        ];
    }
}
