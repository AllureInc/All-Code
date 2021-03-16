<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */
namespace Mangoit\Marketplace\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;


/**
 * Webkul Marketplace admin product controller
 */
class Showsensitiveattrs extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    public function execute()
    {
        $attributs = $this->_view->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('Mangoit_Marketplace::sensitive/attributes.phtml')
                ->toHtml();
        echo $attributs;
        // $this->getResponse()->appendBody($attributs);
        die();
    }
}
