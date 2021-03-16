<?php
namespace Cor\Artist\Block\Adminhtml;
class Artist extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {       
        $this->_controller = 'adminhtml_artist';/*block grid.php directory*/
        $this->_blockGroup = 'Cor_Artist';
        $this->_headerText = __('Artist');
        $this->_addButtonLabel = __('Add New Artist'); 
        parent::_construct();       
    }
}
