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

namespace Ced\Productfaq\Block\Adminhtml\Product\Edit;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Ced\Productfaq\Helper\Data;

class Tabs extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
{
    const BASIC_TAB_GROUP_CODE = 'basic';

    const ADVANCED_TAB_GROUP_CODE = 'advanced';
    const XML_PATH_ENABLED      = 'faq/general/enable_in_frontend';
   
    private $_parent;
    /*
     * Add Faq tab on product eit page
     * @return this
     */
    protected function _prepareLayout()
    {
        $this->parent = parent::_prepareLayout();
        if($this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            $this->addTab(
                'faq',
                [
                'label' => __('FAQS'),
                'content' => $this->_translateHtml(
                    $this->getLayout()->createBlock(
                        'Ced\Productfaq\Block\Adminhtml\Product\Edit\Tab\Faqs'
                    )->toHtml()
                ),
                'group_code' => self::ADVANCED_TAB_GROUP_CODE
                ]
            );
        }
        return $this->parent;
    }
   
}
