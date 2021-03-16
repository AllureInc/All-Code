<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event\Edit\Tab\Renderer;
use Magento\Framework\DataObject;
 
class Statusoption extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) 
    {
        $this->categoryFactory = $categoryFactory;
    }
 
    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        //$this->eventIsClosed($row);
        if ($row->getEventStatus() == 0) {
            return __('Open');
        } else {
            return __('Closed');
        }
    }

    /**
     * Method for close events
     *
     * @return boolean
     */
    public function eventIsClosed($model)
    {
        $endEventDate = $model->getEventEndDate();
        $datetime1 = date_create($endEventDate);
        $datetime2 = date_create(date("Y-m-d"));
        $interval = date_diff($datetime1, $datetime2);
        if ($interval->format('%R%a') > 0) {
            $model->setEventStatus(1);
            $model->save();
        }        
    }
}