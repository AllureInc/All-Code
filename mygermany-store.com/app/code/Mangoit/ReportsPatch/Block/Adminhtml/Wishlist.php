<?php


namespace Mangoit\ReportsPatch\Block\Adminhtml;

class Wishlist extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mangoit_ReportsPatch';
        $this->_controller = 'adminhtml_wishlist';
        $this->_headerText = __('Products in Wishlist');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
