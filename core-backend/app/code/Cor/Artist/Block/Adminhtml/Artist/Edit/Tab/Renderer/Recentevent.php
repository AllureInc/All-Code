<?php 
namespace Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer;
use Magento\Framework\DataObject;

class Recentevent extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $artist_id = $row->getId();
        $eventDates = array();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $eventModel = $objectManager->create('Cor\Eventmanagement\Model\Event');
        $eventArtisModel = $objectManager->create('Cor\Eventmanagement\Model\Eventartist');
        $eventArtistData = $eventArtisModel->getCollection()
            ->addFieldToSelect('event_id')
            ->addFieldToFilter('artist_id', array('eq' => $artist_id))
            ->getData();

        if (!empty($eventArtistData)) {
            foreach ($eventArtistData as $eventArtist) {
                extract($eventArtist);
                $event = $eventModel->load($event_id);
                
                $event_date = $event->getEventStartDate();
                $time = time($event_date);
                
                $eventDates[] = date('Y-m-d', $time);
                $eventModel->unsetData();
            }
            sort($eventDates);
            return $eventDates[0];
        } else {
            return __('No Event(s)');
        }
    }
}
