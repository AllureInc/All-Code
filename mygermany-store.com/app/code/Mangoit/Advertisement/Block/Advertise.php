<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit 
 * @package   Mangoit_MpAdvertisementManager
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2017 Mangoit Software Private Limited (https://mangoit.com)
 * @license   https://store.mangoit.com/license.html
 */
namespace Mangoit\Advertisement\Block;

class Advertise extends \Webkul\MpAdvertisementManager\Block\Advertise
{
    public function blockObjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    public function getIsAdsDemoEnable()
    {
        return $this->_adsHelper->isAdsDemoEnable();
    }

    /**
     * getJsJosnConfig get javascript config
     *
     * @return array
     */
    public function getJsJosnConfig()
    {
        $data = [];
        $data['enableDemoUrl'] = $this->getUrl("mpads/advertise/enableadsdemo", ['_secure' => $this->getRequest()->isSecure()]);
       
        return \Zend_Json_Encoder::encode($data);
    }

    /**
     * getAdsPlans get ad plans
     *
     * @return array
     */
    public function getAdsPlans()
    {
        /*$content_type = [];
        $objectManager = $this->blockObjectManager();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $customerid = $customerSession->getCustomerId();
        $model = $objectManager->create('Webkul\MpAdvertisementManager\Model\Block')->getCollection();
        $data = $model->addFieldToFilter('seller_id', array('eq'=> $customerid));
        foreach ($data as $collections) {
            $content_type[] = $collections->getContentType();
        }
        $dataCollection = $this->_adsHelper->getAdsPlans()->addFieldToFilter('content_type', array('in'=> array_unique($content_type)));*/
        return $this->_adsHelper->getAdsPlans();
        // return $dataCollection;
    }

    /**
     * getPositions get ads placeholders
     *
     * @return array
     */
    public function getPositions()
    {
        return $this->_adsHelper->getAdsPositions();
    }

    /**
     * getPositionLabel the name of the block position according to position id
     *
     * @param [int] $positionId
     * @return String
     */
    public function getPositionLabel($positionId)
    {
        $positions = $this->getPositions();
        foreach ($positions as $pos) {
            if ($pos['value'] == $positionId) {
                return $pos['label'];
                break;
            }
        }
    }

    /**
     * getFormattedPrice
     *
     * @param  decimal $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        return $priceHelper->currency($price, true, false);
    }

    /**
     * getSellerBlocks get current seller blocks
     *
     * @return Mangoit\MpAdvertisementManager\Model\ResourceModel\Block\Collection
     */
    public function getSellerBlocks($contentId = '')
    {
        $blockCollectionObj = $this->_blockCollectionFactory->create();
        $blockCollectionObj->addFieldToFilter(
            'seller_id',
            ['eq'=>$this->_mpHelper->getCustomerId()]
        );
        return $blockCollectionObj->addFieldToFilter(
            'content_type',
            ['eq'=>$contentId]
        );
    }

    /**
     * getAddToCartAction add to cart action
     *
     * @return string
     */
    public function getAddToCartAction()
    {
        return $this->getUrl("mpads/advertise/addtocart", ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getDays ad valid days
     *
     * @param  int $blockId placeholder id
     * @return int
     */
    public function getDays($blockId)
    {
        $settings = $this->_adsHelper->getSettingsById($blockId);
        return $settings['valid_for'];
    }

    /**
     * isAddCanBeBooked is add can be booked
     *
     * @param  int $positionId
     * @return boolean
     */
    public function isAddCanBeBooked($positionId)
    {
        $settings = $this->_adsHelper->getSettingsById($positionId);
        $totalAds = isset($settings['sort_order'])?$settings['sort_order']:1;
        $adsCount = $this->_adsOrderHelper->getBookedAdsCount($positionId);
        if ($adsCount >= $totalAds) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * remaining ads of a block
     *
     * @param  int $positionId
     * @return int
     */
    public function remainingAdsOnParticularBlock($positionId)
    {
        $settings = $this->_adsHelper->getSettingsById($positionId);
        $totalAds = isset($settings['sort_order']) ? $settings['sort_order'] : 1;
        $adsCount = $this->_adsOrderHelper->getBookedAdsCount($positionId);
        $remainingAds = $totalAds - $adsCount ;
        if ($remainingAds < 0) {
            $remainingAds = 0;
        }
        return $remainingAds;
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

    /*
    *
    * will return content type Image/Product/Category/Html
    *
    */    
    public function getContentName($blockPositionId)    
    {
        $_objetcmanager = $this->blockObjectManager();
        $blockPricingModel = $_objetcmanager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $blockPricingModel->load($blockPositionId , 'block_position');
        // echo "<pre>";
        // print_r($blockPricingModel->getData());
        $content = $blockPricingModel->getContentType();
        // echo "content ".$content;
        if ($content == 1) {
            return 'Image/Banner';
        } elseif ($content == 2) {
            return 'Product';
        } elseif ($content == 3) {
            return 'Category';
        } elseif ($content == 4) {
            return 'HTML Editor';
        }
    }
    
    /*
    *
    * will return block type External/Internal
    *
    */

    public function getBlockType($blockPositionId)
    {
        $_objetcmanager = $this->blockObjectManager();
        $blockPricingModel = $_objetcmanager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $blockPricingModel->load($blockPositionId , 'block_position');
        $blockType = $blockPricingModel->getAdType();
        if ($blockType == 0) {
            return 'External';
        } else {
            return 'Internal';
        }
    }

    public function getContentId($blockPositionId)    
    {
        $_objetcmanager = $this->blockObjectManager();
        $blockPricingModel = $_objetcmanager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $blockPricingModel->load($blockPositionId , 'block_position');
        // echo "<pre>";
        // print_r($blockPricingModel->getData());
        $content = $blockPricingModel->getContentType();
        // echo "content ".$content;
        return $content;
    }
}
