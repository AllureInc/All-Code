<?php
namespace Cor\Eventmanagement\Block\Adminhtml;
class Event extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {     
        $this->_controller = 'adminhtml_event';/*block grid.php directory*/
        $this->_blockGroup = 'Cor_Eventmanagement';
        $this->_headerText = __('Event');
        $this->_addButtonLabel = __('Add New Event'); 
        parent::_construct();       
    }
}
