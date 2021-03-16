<?php

namespace Mangoit\Advertisement\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
	protected $_session;
	function __construct( \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectmanager)
	{
		parent::__construct($context);
        $this->_objectManager = $objectmanager;		# code...
        $this->_session = $customerSession;
	}

	public function getBlockPositionLabel($blockPositionId)
    {
        $positions = $this->getAdsPositions();
        foreach ($positions as $pos) {
            if ($pos['value'] == $blockPositionId) {
                return $pos['label'];
                break;
            }
        }
    }
    
    public function getAdsPositions()
    {
        //Get Object Manager Instance
        $positions = $this->_objectManager->create("\Mangoit\Advertisement\Model\Placeholder\Source\Adtype");
        return $positions->toOptionArray();
    }

    public function getAdvertiseContent($blockPositionId)
    {
        $positions = $this->getContentPosition();
        foreach ($positions as $pos) {
            if ($pos['value'] == $blockPositionId) {
                return $pos['label'];
                break;
            }
        }
    }

    public function getProductList()
    {
        $productIds = [];
        $storeCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Product');
        // echo "<pre>";
        $vendorId = $this->_session->getId();
        // echo "<br>vendorId : ".$vendorId;
        $model =  $storeCollection->getCollection()->addFieldToFilter('seller_id', $vendorId)->addFieldToFilter('status', 1)->addFieldToSelect(
                ['mageproduct_id']
            );
        foreach ($model as $key => $value) {
            array_push($productIds, $value['mageproduct_id']);
        }
        // $storeCollection->getCollection()->addFieldToFilter('seller_id', $vendorId);
        return $productIds;

    }


    public function getContentPosition()
    {
        //Get Object Manager Instance
        $positions = $this->_objectManager->create("\Mangoit\Advertisement\Model\Placeholder\Source\Contentname");
        return $positions->toOptionArray();
    }

    public function getPreviousUrl()
    {
        $storeManager = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getBaseUrl();
    }

    

}