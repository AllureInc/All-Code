<?php 
namespace Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer;
use Magento\Framework\DataObject;

class Recentsettlement extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        if (!empty($row->getMostRecentSettlementDate())) {
            return $row->getMostRecentSettlementDate();
        } else {
            return 'N/A';
        }
    }
}