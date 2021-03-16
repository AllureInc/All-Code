<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Banner Page Options
 */
namespace Cnnb\Gtm\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

/**
 * BannerColumn | Admin Block Class
 */
class BannerColumn extends Select
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
     * Provides Banner Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return [
            [
                'label'=> 'select',
                'value'=> ''
            ],
            [
                'label'=> 'HomePage',
                'value'=> 'homepage'
            ],
            [
                'label'=> 'Category Page',
                'value'=> 'categorypage'
            ],
            [
                'label'=> 'Product Details Page',
                'value'=> 'productpage'
            ],
            [
                'label'=> 'Shopping Cart Page',
                'value'=> 'shoppingcart'
            ],
            [
                'label'=> 'Checkout Page',
                'value'=> 'checkout'
            ],
            [
                'label'=> 'CMS Page',
                'value'=> 'cmspage'
            ],
        ];
    }
}
