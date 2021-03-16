<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Ajax Controller
 * Fetch artist name and id on 'onchange' event of Events Dropdown.
 */
namespace Cor\MerchandiseHandling\Controller\Adminhtml\Merchandise;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class EventArtist extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var Cor\Eventmanagement\Model\Eventartist
     */
    protected $eventArtist;

    /**
     * @var Cor\Artist\Model\Artist
     */
    protected $artist;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Cor\Eventmanagement\Model\Eventartist $eventArtist,
        \Cor\Artist\Model\Artist $artist
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->eventArtist = $eventArtist;
        $this->artist = $artist;
    }

    public function execute()
    {
        $response = [];

		$event_id = $this->getRequest()->getParam('event_id');
        $eventArtists = $this->eventArtist->getCollection()->addFieldToFilter('event_id', $event_id);

        foreach ($eventArtists as $eventArtist) {
            $artist_id = $eventArtist['artist_id'];
            $artist = $this->artist->load($artist_id);
            if (!empty($artist->getData())) {
                $response[] = ['value'=> $artist['id'], 'label'=> $artist['artist_name']];
            }
        }
        echo json_encode($response);
    }
}
