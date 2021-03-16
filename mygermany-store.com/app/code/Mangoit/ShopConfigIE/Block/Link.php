<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_ShopConfigIE
 * @author    Mangoit
 */
namespace Mangoit\ShopConfigIE\Block;

use Magento\Framework\View\Element\Html\Link\Current;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**+
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\Marketplace\Helper\Data');
        if (!$helper->isSeller()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}
