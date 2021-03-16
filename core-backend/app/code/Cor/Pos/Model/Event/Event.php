<?php

namespace Cor\Pos\Model\Event;

use Cor\Pos\Api\Event\EventInterface;

/**
 * Class Event
 */
class Event implements EventInterface
{
    /**
     * @var eventFactory
    */
    protected $_eventFactory;

    /**
     * @param \Cor\Eventmanagement\Model\EventFactory $eventFactory
     */
    public function __construct(
        \Cor\Eventmanagement\Model\EventFactory $eventFactory
    ) {
        $this->_eventFactory = $eventFactory;
    }

    /**
     *
     * @api
     * 
     * @return string
     */
    public function getList() {
        $model = $this->_eventFactory->create();
        $collection = $model->getCollection()->addFieldToFilter('event_status', ['eq'=> '0']);
        $eventsData = array();
        foreach ($collection->getData() as $data) {
            $eventsData[] = array(
               'id'=> $data['id'],
               'event_start_date'=> $data['event_start_date'],
               'event_end_date'=> $data['event_end_date'],
               'event_name'=> $data['event_name'],
               'event_street'=> $data['event_street'],
               'event_city'=> $data['event_city'],
               'event_state'=> $data['event_state'],
               'event_zip'=> $data['event_zip'],
               'event_country'=> $data['event_country'],
               'event_phone'=> $data['event_phone'],
               'event_status'=> $data['event_status'],
               'event_time_zone'=> $data['event_time_zone']
            );
        }

        $events = array('events' => $eventsData);
        echo json_encode($events);
        exit;
    }
}
