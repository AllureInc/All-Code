<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Block;

class Advertise extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * $_adsOrderHelper
     *
     * @var \Webkul\MpAdvertisementManager\Helper\Order
     */
    protected $_adsOrderHelper;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context                             $context
     * @param \Webkul\MpAdvertisementManager\Helper\Data                                   $adsHelper
     * @param \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory $collectionFactory
     * @param \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory   $blockCollectionFactory
     * @param \Webkul\Marketplace\Helper\Data                                              $mpHelper
     * @param \Magento\Framework\ObjectManagerInterface                                    $objectmanager
     * @param array                                                                        $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Webkul\MpAdvertisementManager\Helper\Order $adsOrderHelper,
        \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory $collectionFactory,
        \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->_adsHelper = $adsHelper;
        $this->_adsOrderHelper = $adsOrderHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_mpHelper = $mpHelper;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $data);
    }

    /**
     * getIsAdsDemoEnable check if ads demo enabled
     *
     * @return boolean
     */
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
        return $this->_adsHelper->getAdsPlans();
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
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
        return $priceHelper->currency($price, true, false);
    }

    /**
     * getSellerBlocks get current seller blocks
     *
     * @return Webkul\MpAdvertisementManager\Model\ResourceModel\Block\Collection
     */
    public function getSellerBlocks()
    {
        return $this->_blockCollectionFactory->create()->addFieldToFilter(
            'seller_id',
            ['eq'=>$this->_mpHelper->getCustomerId()]
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
        $totalAds = isset($settings['sort_order'])?$settings['sort_order']:1;
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

    /**
     * isModuleEnabled function
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->_adsHelper->getMouduleStatus();
    }
}
