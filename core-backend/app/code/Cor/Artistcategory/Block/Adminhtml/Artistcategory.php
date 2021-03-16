<?php
namespace Cor\Artistcategory\Block\Adminhtml;
class Artistcategory extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {        
        $this->_controller = 'adminhtml_artistcategory';/*block grid.php directory*/
        $this->_blockGroup = 'Cor_Artistcategory';
        $this->_headerText = __('Artistcategory');
        $this->_addButtonLabel = __('Add New Category'); 
        parent::_construct();        
    }
}
