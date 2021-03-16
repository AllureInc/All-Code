<?php
/**
 * Module: Cor_Productmanagement
 * Backend Block File
 * retrieves events associated with particular artist for showing as options of cor_events product attribute.
 */
namespace Cor\Productmanagement\Block\Adminhtml\Artist;

class Eventoptions extends \Magento\Backend\Block\Template
{
    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */
    protected $_objectManager;

    /**
    * @var \Magento\Framework\Registry
    */
    protected $registry;

    /*
    * Class constructor 
    */
    public function __construct(\Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Framework\Registry $registry)
    {
        parent::__construct($context);
        $this->_objectManager = $objectmanager; 
        $this->registry = $registry;
    }

    /*
    * Method for return event artist association 
    * @return event artist association array
    */
    public function getArtistEvents()
    {
        $artistEvents = array();

        /* get post artist_id */
        $artist_id = $this->getRequest()->getParam('artist_id');

        /* get post product_id */
        $product_id = $this->getRequest()->getParam('product_id');

        if ($artist_id) {
            /* event model call for loading eventartist */
            $productModel = $this->_objectManager->create('Magento\Catalog\Model\Product');

            $cor_events = array();

            /* check if product id then load product for events */
            if ($product_id) {
                $product = $productModel->load($product_id);
                if ($artist_id == $product->getCorArtist()) {
                    $corevents = $product->getCorEvents();
                    if ($corevents) {
                        $cor_events = explode(',', $corevents);
                    }
                }
            }

            /* event model call for loading eventartist */
            $model = $this->_objectManager->create('Cor\Eventmanagement\Model\Eventartist');

            $collection = $model->getCollection()
                ->addFieldToFilter('artist_id', $artist_id)
                ->addFieldToSelect('event_id');

            /* event model call for loading events */
            $eventModel = $this->_objectManager->create('Cor\Eventmanagement\Model\Event');

            if (!empty($collection->getData())) {
                foreach ($collection->getData() as $data) {
                    extract($data);
                    $event = $eventModel->load($event_id)->getData();
                    if (!empty($event)) {
                        if ($event['event_status'] == 0) {
                            $artistEvents[] = ['event_id'=> $event_id, 'event_name'=> $event['event_name'], 'selected' => (in_array($event_id, $cor_events) ? true : false)];
                        }
                    }
                }            
            }
        }
        return $artistEvents;
    }
}