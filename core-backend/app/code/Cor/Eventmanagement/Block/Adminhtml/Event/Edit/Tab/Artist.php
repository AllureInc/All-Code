<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event\Edit\Tab;
class Artist extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
  public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) 
    {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Method for retrive event status
     *
     * @return boolean
     */
    public function getEventStatus($id)
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $eventModel = $objectManager->create('Cor\Eventmanagement\Model\Event');
       $eventModel->load($id);
       return $eventModel->getEventStatus();
    }

    /**
     * Method for retrive tax cut off
     *
     * @return collection[]
     */
    public function getArtistCutOffData($id)
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $artistCutOffModel = $objectManager->create('Cor\Eventmanagement\Model\Eventartist');
       $artistCutOffModelCollection = $artistCutOffModel->getCollection()->addFieldToFilter('event_id', $id);
       if (count($artistCutOffModelCollection->getData()) >= 1) {
           return $artistCutOffModelCollection;
       }
    }

    /**
     * Method for retrive artist category
     *
     * @return collection
     */
    public function getCategoriesData()
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $categoryModel = $objectManager->create('Cor\Artistcategory\Model\Artistcategory');
       $collection = $categoryModel->getCollection()->addFieldToFilter('status', ['eq'=> 1]);
       return $collection;
    }

    /**
     * Method for retrive artist
     *
     * @return boolean
     */
    public function getArtistData()
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $artistModel = $objectManager->create('Cor\Artist\Model\Artist');
       $collection = $artistModel->getCollection();
       return $collection;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Artist');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Artist');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}