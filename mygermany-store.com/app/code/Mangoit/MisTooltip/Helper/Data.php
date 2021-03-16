<?php
namespace Mangoit\MisTooltip\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;

/**
* 
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $_toolModel;
    protected $_objectManager;
    protected $_session;

    function __construct( \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Mangoit\MisTooltip\Model\Tooltip $toolModel)
    {
        $this->storeManager = $storeManager;
        $this->_toolModel = $toolModel;
        $this->_objectManager = $objectmanager; 
        parent::__construct($context);
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getToolTipData($store_id, $section_id, $tooltip_id)
    {

        $model = $this->_objectManager->create('Mangoit\MisTooltip\Model\Tooltip');
        $collection = $model->getCollection();
        $store_array = array($store_id, 0);
        $resultArray = [];

        $filteredCollection = $collection->addFieldToFilter('store_id', array('in'=> $store_array))->addFieldToFilter('section_id', array('eq'=> $section_id))->addFieldToFilter('tooltip_id', array('eq'=> $tooltip_id));
        $collectionArray = $filteredCollection->getData();

        if (count($filteredCollection->getData()) > 0) {
            if (count($filteredCollection->getData()) > 1) {
                foreach ($filteredCollection->getData() as $key => $value) {
                    if ($value['store_id'] != $store_id) {
                        unset($collectionArray[$key]);
                    } else {
                        $resultArray[0] = $value;
                    }
                }
                return $resultArray;
            } else {
                return $collectionArray;
            }           
        } else {
            return $collectionArray;
        }
    }

    public function getAllToolTipData($store_id)
    {
        $toolModelCollection = $this->_toolModel->getCollection()->addFieldToFilter('store_id', array('eq'=> $store_id));
        return $toolModelCollection->getData();
    }

    public function getAssociatesOptions($current_store)
    {
        $data = [];
        $blockManager = $this->_objectManager->create('Mangoit\MisTooltip\Block\Adminhtml\Tool\Index');
        $collection = $this->getAllToolTipData($current_store);
        // echo "<pre>";
        foreach ($collection as $key => $value) {
            $option = $blockManager->getSectionOptions($value['section_id']);
            $data[$value['section_id']] = $option;
        }
        return $data;
    }
}