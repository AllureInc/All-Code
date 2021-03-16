<?php 
namespace Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer;
use Magento\Framework\DataObject;

class Wnineoptions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{    
    public function render(DataObject $row)
    {
        if ($row->getWnineReceived() == 0) {
            return __('No');
        } else {
            return __('Yes');
        }
    }
}