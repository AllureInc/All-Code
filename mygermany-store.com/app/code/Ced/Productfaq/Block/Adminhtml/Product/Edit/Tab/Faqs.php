<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Productfaq\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Widget;

class Faqs extends Widget
{


    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/options.phtml';

    /**
     * @return Widget
     */
    protected function _prepareLayout()
    {
        

        $this->addChild('options_box', 'Ced\Productfaq\Block\Adminhtml\Product\Edit\Tab\Faqs\Faq');
       
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    

    /**
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }
}
    