<?php
namespace Cor\Eventmanagement\Controller\Closedevents;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/**
* 
*/
class Index extends Action
{
    protected $_resultPageFactory;
    protected $_objectManager;
    protected $_eventFactory;

    function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Cor\Eventmanagement\Model\EventFactory $eventFactory
        )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_eventFactory = $eventFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $eventModel = $this->_eventFactory->create();
        $eventCollection = $eventModel->getCollection()->addFieldToFilter('event_status', ['eq'=> 0]);
        foreach ($eventCollection->getData() as $item) 
        {
            $datetime1 = date_create($item['event_end_date']);
            $datetime2 = date_create(date("Y-m-d"));
            $interval = date_diff($datetime1, $datetime2);
            if ($interval->format('%R%a') > 0) {
                $eventModel->load($item['id']);
                $eventModel->setEventStatus(1);
                $eventModel->save();
                $eventModel->unsetData();
            }
        }
    }
}