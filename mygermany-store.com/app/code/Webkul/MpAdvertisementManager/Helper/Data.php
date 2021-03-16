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

namespace Webkul\MpAdvertisementManager\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;

/**
 * Webkul MpAdvertisementManager Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ADS_SETTINGS_PATH = "marketplace/ads_settings/ads_config_settings";
    
    const MODULE_STATUS_PATH = "marketplace/ads_settings/mpadvertisementmanager_account";

    const PRODUCT_SKU = "wk_mp_ads_plan";
    
    /**
     * @var \Magento\Config\Model\Config\Backend\Encrypted
     */
    protected $_encrypted;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_sessionManager;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * $_storeManager.
     *
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_json;

    /**
     * @var Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory
     */
    protected $_pricingDataFactory;

    /**
     * @var Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface
     */
    protected $_pricingRepository;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpAdvertisementManager\Api\BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    protected $_searchCreteriaInterface;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context                           $context
     * @param \Magento\Config\Model\Config\Backend\Encrypted                  $encrypted
     * @param \Magento\Framework\Session\SessionManagerInterface              $sessionManager
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param DateTime                                                        $date
     * @param \Magento\Config\Model\ResourceModel\Config                      $resourceConfig
     * @param \Magento\Framework\Json\Helper\Data                             $jsonHelper
     * @param \Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory $pricingDataFactory
     * @param \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface   $pricingRepository
     * @param \Webkul\MpAdvertisementManager\Api\BlockRepositoryInterface     $blockRepository
     * @param \Magento\Framework\Api\SearchCriteriaInterface                  $searchCreteriaInterface
     * @param Session                                                         $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository    $orderRepository
     * @param \Magento\Framework\ObjectManagerInterface                       $objectmanager
     * @param \Magento\Framework\App\Http\Context                             $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DateTime $date,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory $pricingDataFactory,
        \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface $pricingRepository,
        \Webkul\MpAdvertisementManager\Api\BlockRepositoryInterface $blockRepository,
        \Webkul\MpAdvertisementManager\Model\AdsPurchaseBlockDetailFactory $adsPurchaseBlockDetailFactory,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCreteriaInterface,
        Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_encrypted = $encrypted;
        $this->_sessionManager = $sessionManager;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_resourceConfig = $resourceConfig;
        $this->_json = $jsonHelper;
        $this->_pricingRepository = $pricingRepository;
        $this->_pricingDataFactory = $pricingDataFactory;
        $this->_blockRepository = $blockRepository;
        $this->_customerSession = $customerSession;
        $this->_searchCreteriaInterface = $searchCreteriaInterface;
        $this->orderRepository = $orderRepository;
        $this->_objectManager = $objectmanager;
        $this->_adsPurchaseBlockDetailFactory = $adsPurchaseBlockDetailFactory;
        $this->_httpContext = $httpContext;
        parent::__construct($context);
    }

    /**
     * getCanDebug can create log.
     *
     * @return int
     */
    public function getCanDebug()
    {
        return 1;
    }

    /**
     * getSettingsById get the setting value from configuration according to block id
     *
     * @param  int $blockId
     * @return int
     */
    public function getSettingsById($blockId)
    {
        $settings = $this->getAdsSettings();
        if (isset($settings[$blockId])) {
            return $settings[$blockId];
        } else {
            return false;
        }
    }

    /**
     * isAdsDemoEnable
     *
     * @return int|null
     */
    public function isAdsDemoEnable()
    {
        return $this->_httpContext->getValue('demo_ads_status');
    }

    /**
     * getMouduleStatus function get the status of the module from configuration
     *
     * @return int
     */
    public function getMouduleStatus()
    {
        $data = $this->getConfigData(self::MODULE_STATUS_PATH);
        return $data;
    }
    
    /**
     * saveAdsSettings get ads setting from configuration
     *
     * @param  array $paramname
     * @return array
     */
    public function getAdsSettings()
    {
        try {
            $data = $this->getConfigData(self::ADS_SETTINGS_PATH);
            return $jsonData = $this->_json->jsonDecode($data);
        } catch (\Exception $e) {
            return false;
            $this->debugAdsManager("Error in saving ads settings: ", $e->getTrace());
        }
    }

    /**
     * saveAdsSettings save ads setting in configuration
     *
     * @param  array $data
     * @return boolean true|false
     */
    public function saveAdsSettings($data)
    {
        try {
            $jsonData = $this->_json->jsonEncode($data);
            $this->_resourceConfig
                ->saveConfig(
                    self::ADS_SETTINGS_PATH,
                    $jsonData,
                    'default',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            return true;
        } catch (\Exception $e) {
            return false;
            $this->debugAdsManager("Error in saving ads settings: ", $e->getTrace());
        }
    }

    /**
     * getPositionLabel get position label
     *
     * @return string
     */
    public function getPositionLabel($positionId)
    {
        $positions = $this->getAdsPositions();
        foreach ($positions as $position) {
            if ($position['value'] == $positionId) {
                return $position['label'];
            }
        }
        return false;
    }

    /**
     * getAdsPositions get ads positions
     *
     * @return array
     */
    public function getAdsPositions()
    {
        $positions = $this->_objectManager-> create("\Webkul\MpAdvertisementManager\Model\Placeholder\Source\Positions");
        return $positions->toOptionArray();
    }

    /**
     * getCurrentStore get current store id.
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    /**
     * getUrl
     *
     * @param  string $dir
     * @return string
     */
    public function getUrl($dir)
    {
        return $this->_storeManager->getStore()->getBaseUrl($dir);
    }

    /**
     * getPageUrl
     *
     * @param  string $path
     * @param  array  $params
     * @return string
     */
    public function getPageUrl($path, $params = [])
    {
        return $this->_storeManager->getStore()->getUrl($path, $params);
    }

    /**
     * getMediaUrl
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * getHeight
     *
     * @return string
     */
    public function getHeightConfig()
    {
        $height =  $this->scopeConfig
                         ->getValue(
                             'marketplace/ads_settings/ads_height',
                             \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                         );
        return $height;
    }

    /**
     * get Auto play time
     *
     * @return string
     */
    public function getAutoPlayTime()
    {
        $autoPlayTime =  $this->scopeConfig
                         ->getValue(
                             'marketplace/ads_settings/ads_slider_time',
                             \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                         );
        return $autoPlayTime;
    }

    /**
     * getConfigData get config value by key
     *
     * @param  string $path
     * @param  int    $scope
     * @return mixed
     */
    public function getConfigData(
        $path,
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        return $this->scopeConfig->getValue(
            $path,
            $scope
        );
    }

    /**
     * debugAdsManager create log for ads manager
     *
     * @param  string $msg
     * @param  array  $context error trace
     * @return void
     */
    public function debugAdsManager($msg, $context)
    {
        if ($this->getCanDebug()) {
            $myLogger = $this->_objectManager->get("\Webkul\MpAdvertisementManager\Logger\AdsLogger");
            $myLogger->debug($msg, $context);
        }
    }

    /**
     * getAdsPlans get ads plans
     *
     * @return Pricing\Collection
     */
    public function getAdsPlans()
    {
        return $this->_pricingDataFactory->create()->getCollection();
    }

    /**
     * getCartProductOptions get ads options
     *
     * @param  int $itemId
     * @return array
     */
    public function getCartProductOptions($itemId)
    {
        $item = $this->_objectManager->create("\Magento\Quote\Model\Quote\ItemFactory")->create()->load($itemId);
        
        if ($item->getSku() == self::PRODUCT_SKU) {
            return $options = $item->getOptionByCode("info_buyRequest");
        }
    }

    /**
     * getAdBlockHtml get ad block html
     *
     * @param  int $blockId ad block id
     * @return mixed string|boolean
     */
    public function getAdBlockHtml($blockId)
    {
        try {
            $block = $this->_blockRepository->getById($blockId);
            return ['id'=>$block->getId(),'seller_id'=>$block->getSellerId(),'image_name'=>$block->getImageName(),'url'=>$block->getUrl()];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * getAdBlockHtmlDetail get ad block html
     *
     * @param  int $adsPurchaseDetailId ad block id
     * @return mixed array|boolean
     */
    public function getAdBlockHtmlDetail($adsPurchaseDetailId)
    {
        $collection = $this->_adsPurchaseBlockDetailFactory->create()->getCollection();
        $joinTable = $collection->getResource()->getTable("marketplace_ads_purchase_details");
        $collection->getSelect()->join(
            ['mapd' => $joinTable],
            'main_table.ads_purchase_detail_id = mapd.id'
        );
        $collection->addFieldToFilter('mapd.id', ['eq'=>$adsPurchaseDetailId]);
        $data = [];
        try {
            if ($collection->getSize() == 1) {
                foreach ($collection as $model) {
                    $data = ['id'=>$model->getBlock(),'seller_id'=>$model->getSellerId(),'image_name'=>$model->getImageName(),'url'=>$model->getUrl()];
                }
                return $data;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * checkPricingExist check if ads pricing exist for block position
     *
     * @param  int $blockPosition
     * @return boolean
     */
    public function checkPricingExist($blockPosition)
    {
        $collection = $this->_pricingRepository->getList($this->_searchCreteriaInterface);
        $collection->addFieldToFilter('block_position', ['eq' => $blockPosition]);

        if ($collection->getSize() > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * getBlockLabel get block title
     *
     * @param  int $blockId
     * @return string
     */
    public function getBlockLabel($blockId)
    {
        try {
            $block = $this->_blockRepository->getById($blockId);
            return $block->getTitle();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * getBlockLabel get block title
     *
     * @param  int $blockId
     * @return string
     */
    public function getBlockPosition($blockId)
    {
        try {
            $block = $this->_pricingRepository->getById($blockId);
            return $block->getBlockPosition();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * get Block position label
     *
     * @param  int $blockPositionId
     * @return String
     */
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

    /**
     * getIncrementOrderId function get the increment id from order id
     *
     * @param int $orderId
     * @return int
     */
    public function getIncrementOrderId($orderId = null)
    {
        $order = $this->orderRepository->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        return $orderIncrementId;
    }

    /**
     * getAllAdsPositions function return the list of all ads position
     *
     * @return array
     */
    public function getAllAdsPositions()
    {
        $options = [
            [
                'label' => "Please select a position",
                'value' => null
            ],
            [
                'label' => __("Home Seller Ads Page Top"),
                'value' => 1,
            ],
            [
                'label' => __("Home Seller Popup Ads"),
                'value' => 2,
            ],
            [
                'label' => __("Home Seller Ads Page Bottom Container"),
                'value' => 3,
            ],
            [
                'label' => "Category Seller Ads Page Top",
                'value' => 4,
            ],
            [
                'label' => "Category Seller Ads Page Bottom Container",
                'value' => 5,
            ],
            [
                'label' => "Category Seller Ads Main",
                'value' => 6,
            ],
            [
                'label' => "Category Seller Ads Div Sidebar Main Before",
                'value' => 7,
            ],
            [
                'label' => "Category Seller Ads Div Sidebar Main After",
                'value' => 8,
            ],
            [
                'label' => "Catalog Product Seller Ads Page Top",
                'value' => 9,
            ],
            [
                'label' => "Catalog Product Seller Ads Page Bottom Container",
                'value' => 10,
            ],
            [
                'label' => "Home Seller Ads Product Main Info",
                'value' => 11,
            ],
            [
                'label' => "Catalog Search Seller Ads Page Top",
                'value' => 12,
            ],
            [
                'label' => "Catalog Search Seller Ads Page Bottom Container",
                'value' => 13,
            ],
            [
                'label' => "Catalog Search Seller Ads Main",
                'value' => 14,
            ],
            [
                'label' => "Catalog Search Seller Ads div Sidebar Main Before",
                'value' => 15,
            ],
            [
                'label' => "Catalog Search Seller Ads div Sidebar Main After",
                'value' => 16,
            ],
            [
                'label' => "Checkout cart Seller Ads Page Top",
                'value' => 17,
            ],
            [
                'label' => "Checkout cart Seller Ads Page bottom Container",
                'value' => 18,
            ],
            [
                'label' => "Checkout Seller Ads Page Top",
                'value' => 19,
            ],
            [
                'label' => "Checkout Seller Ads Page bottom Container",
                'value' => 20,
            ]
        ];
        return $options;
    }
}
