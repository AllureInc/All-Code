<?php
namespace Cor\Artistcategory\Block\Adminhtml\Artistcategory\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {        
        parent::_construct();
        $this->setId('checkmodule_artistcategory_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Artist Category Information'));
    }
}