<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('checkmodule_event_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Event Information'));
    }
}