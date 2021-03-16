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
 * Webkul MpAdvertisementManager Helper Order.
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ADS_SETTINGS_PATH = "marketplace/ads_settings/ads_config_settings";

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
    protected $_orderFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;

    /**
     * [__construct
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
     * @param \Webkul\MpAdvertisementManager\Helper\Data                      $helper
     * @param \Magento\Sales\Model\OrderFactory                               $orderFactory
     * @param \Magento\Framework\ObjectManagerInterface                       $objectManager
     * @param Session                                                         $customerSession
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
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Session $customerSession
    ) {
        $this->_encrypted = $encrypted;
        $this->_sessionManager = $sessionManager;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_resourceConfig = $resourceConfig;
        $this->_json = $jsonHelper;
        $this->_pricingRepository = $pricingRepository;
        $this->_pricingDataFactory = $pricingDataFactory;
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * getBookedAdsCount get ads count
     *
     * @param  int $position
     * @return int
     */
    public function getBookedAdsCount($position)
    {
        $ads = $this->getCountOfSellerAds($this->getAllSellersIds(), $position);
        return count($ads);
    }
    
    /**
     * getAllSellersIds get all seller ids
     *
     * @return array seller ids
     */
    public function getAllSellersIds()
    {
        $ids = [];
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\SellerFactory'
        )->create()
            ->getCollection()
            ->addFieldToFilter("is_seller", 1);
        $idsSelect = $collection->getSelect();
        $idsSelect->reset(\Zend_Db_Select::ORDER);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(\Zend_Db_Select::COLUMNS);
        $idsSelect->columns('seller_id', 'main_table');
        $ids = $collection->getConnection()->fetchCol($idsSelect);
        return $ids;
    }

    /**
     * getInvoceItemValidity get invoiced item validity
     *
     * @param $order Magento\Sales\Model\Order
     * @param $orderItemId int
     * @param $days valid days
     *
     * @return boolean true|false
     */
    public function getInvoceItemValidity($orderId, $orderItemId, $days)
    {
        $order = $this->_orderFactory->create()->load($orderId);
        $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
        $orderInvoiceItemTable = $connection->getTableName('sales_invoice_item');
        $invoices = $order->getInvoiceCollection()
        ->join(
            ['sii' => $orderInvoiceItemTable],
            "main_table.entity_id = sii.parent_id",
            [
                'main_table.entity_id',
                'main_table.order_id',
                'main_table.created_at',
                'sii.order_item_id'
            ]
        )
        ->addFieldToFilter('sii.order_item_id', ['in'=>[$orderItemId]]);
        if (count($invoices) > 0) {
            foreach ($invoices as $res) {
                return $this->validateDate($res['created_at'], $days);
            }
        }

        return false;
    }

    /**
     * validateDate validate ads validity
     *
     * @param $date string
     * @param $days valid days
     *
     * @return boolean true|false
     */
    public function validateDate($date, $days)
    {

        $dateTime = $this->_objectManager->create("\Magento\Framework\Stdlib\DateTime\DateTime");

        $currentTime = $dateTime->timeStamp();

        $validDate = strtotime("$date +$days day");
        if ($currentTime >= $validDate) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * getSellerAds get seller ad position wise
     *
     * @param $customerIds array of customer ids
     * @param $position int position id
     *
     * @return array ads data
     */
    public function getSellerAds($customerIds, $position, $orderDetail = 0)
    {
        $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');
        $adsDatas = $model->create()->getCollection()
                                                ->addFieldToFilter('block_position', ['eq' => $position])
                                                ->addFieldToFilter('seller_id', ['in' => $customerIds])
                                                ->addFieldToFilter('invoice_generated', ['eq' => 1])
                                                ->addFieldToFilter('enable', ['eq' => 1])
                                                ->addFieldToFilter('store_id', $this->_helper->getCurrentStoreId());
        $ads = [];
        foreach ($adsDatas as $adsData) {
            $validity = $this->getInvoceItemValidity($adsData['order_id'], $adsData['item_id'], $adsData['valid_for']);
            if ($validity) {
                $ads[$adsData['item_id']] =['id'=>$adsData['id'], 'block_position'=>$adsData['block_position'], 'price'=>$adsData['price'], 'days'=>$adsData['valid_for'],
                'block'=>$adsData['block']];
            }
        }
        return $ads;
    }
    
    /**
     * getCountOfSellerAds get count of seller ads according to the position
     *
     * @param $customerIds array of customer ids
     * @param $position int position id
     * @return int
     */
    public function getCountOfSellerAds($customerIds, $position)
    {
        $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');
        $adsDatas = $model->create()->getCollection()
                                                ->addFieldToFilter('block_position', ['eq' => $position])
                                                ->addFieldToFilter('seller_id', ['in' => $customerIds])
                                                ->addFieldToFilter('main_table.store_id', $this->_helper->getCurrentStoreId());
        $joinTable = $adsDatas->getResource()->getTable("sales_order");
        $adsDatas->getSelect()->join(
            ['so' => $joinTable],
            'main_table.order_id = so.entity_id',
            ['so.state']
        );
        $adsDatas->addFieldToFilter('so.state', ['nin'=>['canceled']]);
        $ads = [];
        foreach ($adsDatas as $adsData) {
            $validity = $this->validateDate($adsData['created_at'], $adsData['valid_for']);
            if ($validity) {
                $ads[$adsData['item_id']] =['block_position'=>$adsData['block_position'], 'price'=>$adsData['price'], 'days'=>$adsData['valid_for'],
                'block'=>$adsData['block']];
            }
        }
        return $ads;
    }

    /**
     * getAdsOrderIds get the order id of all ads type product purchase item
     *
     * @param $position int position id
     * @return array
     */
    public function getAdsOrderIds($position)
    {
        $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');
        $adsDatas = $model->create()->getCollection()
                                                ->addFieldToFilter('block_position', ['eq' => $position]);
        return $adsDatas->getColumnValues('order_id');
    }
}
