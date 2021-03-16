<?php

namespace Mangoit\Advertisement\Block;

use Magento\Customer\Model\Session;

class Ads extends \Webkul\MpAdvertisementManager\Block\Ads
{


    /**
     * setTemplate set template according to demo status
     *
     * @param  $template
     * @return void
     */
    /*
    public function setTemplate($template)
    {

        if($this->isAdsDemoEnabled() === 1) {

            parent::setTemplate($template);

        } elseif($this->isAdsDemoEnabled() === 0) {

            parent::setTemplate($template);

        } else {

            parent::setTemplate($template);
        }
    }
    */

    /**
     * isAdsDemoEnabled is ads demo enabled for seller
     *
     * @return boolean
     */
    public function isAdsDemoEnabled()
    {

        return $this->_adsHelper->isAdsDemoEnable();
    }

    /**
     * Render block position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_data['position'];
    }

    /**
     * getPositionLabel get position label
     *
     * @return string
     */
    public function getPositionLabel()
    {
        return $this->_adsHelper->getPositionLabel($this->_data['position']);
    }

    /**
     * get block settings.
     *
     * @return array
     */
    public function getBlockSettings()
    {
        return $this->_adsHelper->getSettingsById($this->getPosition());
    }

    /**
     * getAdsCount get ads count
     *
     * @return int
     */
    public function getAdsCount()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['sort_order'])) {
            return $setting['sort_order'];
        }

        return false;
    }

    /**
     * getValidDays get valid days
     *
     * @return int|boolean
     */
    public function getValidDays()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['valid_for'])) {
            return $setting['valid_for'];
        }

        return false;
    }

    /**
     * getWidth ad width
     *
     * @return string|boolean
     */
    public function getWidth()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['width']) && $setting['width_type'] == 'custom') {
            return $setting['width'];
        } else {
            return 'full';
        }

        return false;
    }

    /**
     * getHeight get ad height
     *
     * @return string|boolean
     */
    public function getHeight()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['height']) && $setting['height']) {
            return $setting['height'];
        }

        return '300px';
    }

    /**
     * getHeight get ad height
     *
     * @return string|boolean
     */
    public function getHeightConfig()
    {
        return $this->_adsHelper->getHeightConfig();
    }

    /**
     * get Auto play time
     *
     * @return string|boolean
     */
    public function getAutoPlayTime()
    {
        return $this->_adsHelper->getAutoPlayTime();
    }

    /**
     * getSellerIds get seller ids
     *
     * @return array seller ids
     */
    public function getSellerIds()
    {
        return $this->_adsOrderHelper->getAllSellersIds();
    }

    /**
     * getCurrentAds get current ads
     *
     * @return array
     */
    public function getCurrentAds()
    {
        /*echo "<pre>";
        echo "<br> seller_id ";
        print_r($this->getSellerIds());
        echo "<br> position  ";
        print_r($this->getPosition());
        die();*/
        $adBlocks = $this->_adsOrderHelper->getSellerAds($this->getSellerIds(), $this->getPosition());
        // echo "<pre>";
        // print_r($adBlocks);
        // die();
        $ads = [];
        foreach ($adBlocks as $adBlock) {
            $ads[] =$this->_adsHelper->getAdBlockHtml($adBlock['block']);
        }

        return $ads;
    }

    /**
     * get media url
     *
     * @return String
     */
    public function getMediaUrl()
    {
        return $this->_adsHelper->getMediaUrl();
    }

    /**
     * getSessionVal get the value set in session for random showing pop up ads
     *
     * @return int
     */
    public function getSessionVal()
    {
        return $this->_session->getWebkulMpAdvPopupAdsSessionVal();
    }

    /**
     * setSessionVal set the value in session for random showing pop up ads
     *
     * @param [int] $val
     * @return int
     */
    public function setSessionVal($val)
    {
        $this->_session->setWebkulMpAdvPopupAdsSessionVal($val);
    }

    public function getAdContent($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $contentModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        $contentModel->load($id);
        $content = $contentModel->getContentType();
        return $content;
    }

    public function storeId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeObjManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $currentStoreId = $storeObjManager->getStore()->getId();
        return $currentStoreId;
    }

    public function getBlockStoreId($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $contentModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
        $contentModel->load($id, 'block');
        $blockStoreId = $contentModel->getStoreId();
        return $blockStoreId;
    }

    public function isAdminAd($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $contentModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $contentModel->load($id, 'block_position');
        $result = $contentModel->getAdType();
        return $result;
    }

    public function getStoreWiseAds($id, $storeId) 
    {
        // echo "<br> id : ".$id;
        // echo "<br> storeId : ".$storeId;
        $webkulBlockIdsArray = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $webkulAdsModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        $adminAdsModel = $objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        $adminAdsModelCollection = $adminAdsModel->getCollection();

        $filteredCollec = $adminAdsModelCollection->addFieldToFilter('block_position', ['eq'=>$id])->addFieldToFilter('store_id', ['in'=> ['All Store', $storeId] ])->addFieldToFilter('enable', ['eq'=> '1']);
        if ($filteredCollec->getData()) {
            foreach ($filteredCollec->getData() as $item) {
                array_push($webkulBlockIdsArray, $item['webkul_block_id']);
                // echo "<br> item ".$item['webkul_block_id'];
            }

            return $webkulBlockIdsArray;
        } else {
           return $webkulBlockIdsArray;
        }
    }

    public function getAdminAdsStoreWise($allAds)
    {
        $advertiseArray = [];
        // print_r($allAds);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $webkulAdsModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        $adminAdsModel = $objectManager->create('Mangoit\Advertisement\Model\Adsadmin');

        foreach ($allAds as $value) {
            $webkulAdsModel->load($value);
            $adminAdsModel->load($value, 'webkul_block_id');
            $storeId = $adminAdsModel->getStoreId();
            array_push($advertiseArray, array('id' => $webkulAdsModel['id'], 'seller_id' => $webkulAdsModel['seller_id'], 'image_name' => $webkulAdsModel['image_name'], 'url' => $webkulAdsModel['url'], 'store_id'=> $storeId));

        }
        return $advertiseArray;

    }
}
