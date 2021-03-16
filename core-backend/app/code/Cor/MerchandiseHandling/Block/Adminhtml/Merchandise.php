<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Block File
 * retrieves event and associated artist .
 */
namespace Cor\MerchandiseHandling\Block\Adminhtml;
class Merchandise extends \Magento\Backend\Block\Template
{
    /**
     * Method to retrieve artists list.
     *
     * @return artistlist
     */
    public function getArtistsList() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $artistmodel = $objectManager->create('Cor\Artist\Model\Artist');
        $collection = $artistmodel->getCollection();
        $artists = $collection->getData();
        return $artists;
    }

    /**
     * Method to retrieve events list.
     *
     * @return eventlist
     */
    public function getEventsList() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eventmodel = $objectManager->create('Cor\Eventmanagement\Model\Event');
        $collection = $eventmodel->getCollection();
        $events = $collection->getData();
        return $events;
    }
}
