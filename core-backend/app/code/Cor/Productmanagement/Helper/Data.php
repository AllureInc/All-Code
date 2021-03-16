<?php
/**
 * Module: Cor_Productmanagement
 * Backend Helper
 * Fetch artist and event collection.
 */
namespace Cor\Productmanagement\Helper;
use Magento\Customer\Model\Session as CustomerSession;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @var \Magento\Backend\Model\UrlInterface
    */
    protected $_objectManager;
    /**
    * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
    */
    protected $attributeSet;
    /**
    * @var \Magento\Catalog\Model\Session
    */
    protected $_catalogSession;
    /**
    * @var \Cor\Artist\Model\ArtistFactory
    */
    protected $artistFactory;
    /**
    * @var \Magento\Framework\App\Request\Http
    */
    protected $request;

    /**
     * Class constructor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Model\Session $catalogSession,
        \Cor\Artist\Model\ArtistFactory $artistFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->attributeSet = $attributeSet;
        $this->_catalogSession = $catalogSession;
        $this->artistFactory = $artistFactory;
        $this->request = $request;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Method for retrive artist list
     * @return artist[]
     */
    public function getArtistsList(){
        $collection = $this->getArtistsModel()->getCollection();
        $collection = $collection->addFieldToSelect(array('id', 'artist_name'));
        $artists = $collection->getData();
        return $artists;
    }

    /**
     * Method for retrive category list
     * @return category[]
     */
    public function getArtistCategoriesList(){
        $collection = $this->getArtistCategoryModel()->getCollection();
        $artistcategories = $collection->getData();
        return $artistcategories;
    }

    /**
     * Method for retrive event list
     * @return event
     */
    public function getCorEventsList(){
        $collection = $this->getCorEventsModel()->getCollection();
        $corEvents = $collection->getData();
        return $corEvents;
    }

    /**
     * Method for create user factory
     * @return $this->artistFactory
     */
    public function getArtistsModel(){
        return $this->artistFactory->create();
    }

    /**
     * Method for create object of Artistcategory model
     * @return object
     */
    public function getArtistCategoryModel(){
        return $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
    }

    /**
     * Method for create object of event model
     * @return object
     */
    public function getCorEventsModel(){
        return $this->_objectManager->create('Cor\Eventmanagement\Model\Event');
    }
}