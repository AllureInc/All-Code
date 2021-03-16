<?php
/**
 * Mpreport system Helper Data
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpreportsystem\Helper;

use Webkul\Mpreportsystem\Model\Product as SellerProduct;
use Magento\Sales\Model\ResourceModel\Order;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Webkul\Marketplace\Model\ResourceModel;

/**
 * Webkul Mpreportsystem Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_marketplaceProductCollection;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $_salesStatusCollection;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $_marketplacesaleslist;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $_mpsaleslistCollection;

    /**
     * @var Magento\Framework\Locale\ListsInterface
     */
    protected $_listInterface;

    /**
     * @var Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var string
     */
    protected $_deploymentConfigDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_storeTime;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param \Framework\App\Helper\Context       $context
     * @param \Magento\Customer\Model\Session     $customerSession
     * @param \Catalog\Model\ProductFactory       $productFactory
     * @param \StoreManagerInterface              $storeManager
     * @param \Magento\Directory\Model\currency   $currency
     * @param \CurrencyInterface                  $localeCurrency
     * @param Product\CollectionFactory           $marketplaceProductCollection
     * @param Order\Status\CollectionFactory      $salesStatusCollection
     * @param \Catalog\Model\CategoryFactory      $categoryFactory
     * @param \Product\CollectionFactory          $productCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory   $orderFactory
     * @param SaleslistFactory                    $saleslistFactory
     * @param ListsInterface                      $listInterface
     * @param RegionFactory                       $regionFactory
     * @param deploymentConfig                    $deploymentConfig
     * @param \Webkul\Marketplace\Helper\Data     $mpHelper
     * @param \TimezoneInterface                  $timezone
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        ResourceModel\Product\CollectionFactory $marketplaceProductCollection,
        Order\Status\CollectionFactory $salesStatusCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        SaleslistFactory $saleslistFactory,
        ResourceModel\Saleslist\CollectionFactory $mpsaleslistFactory,
        ListsInterface $listInterface,
        RegionFactory $regionFactory,
        DeploymentConfig $deploymentConfig,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_customerSession = $customerSession;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->_marketplaceProductCollection = $marketplaceProductCollection;
        $this->_salesStatusCollection = $salesStatusCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderFactory = $orderFactory;
        $this->_marketplacesaleslist = $saleslistFactory;
        $this->_mpsaleslistCollection = $mpsaleslistFactory;
        $this->_listInterface = $listInterface;
        $this->_regionFactory = $regionFactory;
        $this->_deploymentConfigDate = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE
        );
        $this->_mpHelper = $mpHelper;
        $this->_storeTime = $timezone;
    }

    // get cutsomer id by customer session
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }
    
    // get all product ids of seller
    public function getSellerProductIds()
    {
        $sellerId = $this->getCustomerId();
        $productIds = [];
        if ($sellerId) {
            $productCollection = $this->_marketplaceProductCollection->create()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($productCollection as $product) {
                $productIds[] = $product->getMageproductId();
            }
        }
        return $productIds;
    }

    // get all category ids
    public function getAllCategoriesId()
    {
        $categoryIds = [];
        $categoryCollection = $this->_categoryFactory->create()
            ->getCollection();
        foreach ($categoryCollection as $category) {
            $categoryIds[] = $category->getId();
        }
        return $categoryIds;
    }

    // get all category ids of an product by product id
    public function getCategoryIdsByProductId($productId)
    {
        $categoryIds = [];
        $productModel = $this->_productFactory->create()
            ->load($productId);
        if ($productModel->getCategoryIds() &&
            count($productModel->getCategoryIds())
        ) {
            $categoryIds = array_unique($productModel->getCategoryIds());
        }
        return $categoryIds;
    }

    // get collection of all products of category ids
    public function getCategoryIdsByProductIds($productIds)
    {
        $categoryIdarray = [];
        if (count($productIds)) {
            foreach ($productIds as $productId) {
                $categoryIds = $this->getCategoryIdsByProductId($productId);
                foreach ($categoryIds as $categoryKey => $categoryId) {
                    if (!in_array($categoryId, $categoryIdarray)) {
                        $categoryIdarray[] = $categoryId;
                    }
                }
            }
        }
        return $categoryIdarray;
    }

    // get all order status
    public function getOrderStatus()
    {
        $orderStatusUpdatedArray = [];
        $orderStatusArray = $this->_salesStatusCollection
            ->create()->toOptionArray();
        foreach ($orderStatusArray as $key => $orderStatus) {
            if ($orderStatus['value']!='closed') {
                $orderStatusUpdatedArray[$orderStatus['value']] =
                $orderStatus['label'];
            }
        }
        return $orderStatusUpdatedArray;
    }

    // get all productids of categories which are in an array
    public function getProductIdsByCategoryIds($categoryIds = [])
    {
        $productIds = [];
        if (count($categoryIds)) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $categoryIds]);
            foreach ($collection as $productModel) {
                $productIds[] = $productModel->getEntityId();
            }
        }
        return $productIds;
    }

    // get category collection either for admin or for seller
    public function getCategoriesCollection($flag)
    {
        $categoryArray = [];
        if ($flag==0) {
            $sellerProductIds = $this->getSellerProductIds();
            $categories = $this->getCategoryIdsByProductIds($sellerProductIds);
        } else {
            $categories = $this->getAllCategoriesId();
        }
        $categoryCollection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$categories]);
        foreach ($categoryCollection as $category) {
            $categoryData = $this->getCategory($category->getEntityId());
            $categoryArray[$category->getEntityId()] = $categoryData->getName();
        }
        return $categoryArray;
    }

    // load an category by category id
    public function getCategory($categoryId)
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    // get product collection data for top selling product graph
    public function getProductsSalesCollection($data)
    {
        $quoteitemTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('quote_item');
        $productCount = $this->gettopPtoductCountSetting();
        $productQuantityCollection = [];
        $collection = $this->getProductCollectionByFilter($data);
        $collection->getSelect()
            ->columns('SUM(magequantity) AS qty')
            ->group('mageproduct_id');

        $collection->setOrder('qty', 'DESC');
        $collection->setPageSize($productCount)
            ->setCurPage(1);
        if ($collection->getSize()) {
            foreach ($collection as $salesItem) {
                $productId = $salesItem->getMageproductId();
                if (array_key_exists($productId, $productQuantityCollection)) {
                    $productQuantityCollection[$productId]['qty'] =
                    $productQuantityCollection[$productId]['qty'] + $salesItem->getQty();
                } else {
                    $productQuantityCollection[$productId]['name'] = $salesItem->getmageproName();
                    $productQuantityCollection[$productId]['qty'] = $salesItem->getQty();
                }
            }
        }
        return $this->getFormattedData1($productQuantityCollection);
    }

    // format product collection data for graph
    public function getFormattedData1($data)
    {
        $returnData = [];
        $returnKey = [];
        $result = [];
        $returnName = [];
        $totalQty = 0;
        if (is_array($data)) {
            foreach ($data as $datakey => $datavalue) {
                $returnData[] = $datavalue['qty'];
                $returnKey[] = $datakey;
                $returnName[] = $datavalue['name'];
                $totalQty = $totalQty + $datavalue['qty'];
            }
        }
        $result['returnData'] = $returnData;
        $result['returnKey'] = $returnKey;
        $result['totalQty'] = $totalQty;
        $result['name'] = $returnName;
        return $result;
    }

    // get saleslist collecion by filter
    public function getProductCollectionByFilter($data)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerIdFlag = 0;
        if ($sellerId!='') {
            $sellerIdFlag = 1;
        }
        $orderCollection = $this->_mpsaleslistCollection
            ->create();

        if ($orderCollection->getSize()) {
            $orderCollection->addFieldToFilter(
                'main_table.parent_item_id',
                ['null' =>  true]
            );

            if ($sellerIdFlag) {
                $orderCollection->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            }
            if ($data['filter']=='month') {
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => date('Y-m').'-01 00:00:00',
                        'to' => date('Y-m').'-31 23:59:59',
                    ]
                );
            } elseif ($data['filter']=='week') {
                $firstDayOfWeek = date('Y-m-d', strtotime('Last Monday', time()));
                $lastDayOfWeek = date('Y-m-d', strtotime('Next Sunday', time()));
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => $firstDayOfWeek.' 00:00:00',
                        'to' => $lastDayOfWeek.' 23:59:59',
                    ]
                );
            } elseif ($data['filter']=='day') {
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => date('Y-m-d').' 00:00:00',
                        'to' => date('Y-m-d').' 23:59:59',
                    ]
                );
            } else {
                $curryear = date('Y');
                $date1 = $curryear.'-01-01 00:00:00';
                $date2 = $curryear.'-12-31 23:59:59';
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => $date1,
                        'to' => $date2,
                    ]
                );
            }
        }
        return $orderCollection;
    }

    // get country sales collection data for geo location graph
    public function getCountrySalesCollection($data)
    {
        $productQuantityCollection = [];
        $sellerOrderCollection = $this->getProductCollectionByFilter($data);
        $orderSaleArr = [];
        $orderIds = [];

        foreach ($sellerOrderCollection as $record) {
            $orderId = $record->getOrderId();
            $orderIds[] = $record->getOrderId();
            if (!isset($orderSaleArr[$record->getOrderId()])) {
                $orderSaleArr[$record->getOrderId()] =
                $record->getActualSellerAmount() + $record->getTotalTax();
            } else {
                $orderSaleArr[$orderId] =
                $orderSaleArr[$orderId] + $record->getActualSellerAmount() + $record->getTotalTax();
            }
        }
        $updatedOrderIds = array_unique($orderIds);
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $orderIds]
            );
        $returnData = $this->getArrayData($orderCollection, $orderSaleArr);
        return $returnData;
    }

    // get formatted data for geo location graph
    public function getArrayData($collection, $orderSaleArr)
    {
        $countryArr = [];
        $countryRegionArr = [];
        $countrySaleArr = [];
        $countryOrderCountArr = [];
        foreach ($collection as $record) {
            $addressData = $record->getBillingAddress()->getData();
            $countryId = $addressData['country_id'];
            $countryName =$this->_listInterface
                ->getCountryTranslation($countryId);
            $countryArr[$countryId] = $countryName;
            if (isset($orderSaleArr[$record->getId()])) {
                if (!isset($countryRegionArr[$countryId])) {
                    $countryRegionArr[$countryId] = [];
                }
                if (!isset($countrySaleArr[$countryId])) {
                    $countrySaleArr[$countryId] = [];
                }
                if (!isset($countryOrderCountArr[$countryId])) {
                    $countryOrderCountArr[$countryId] = [];
                }
                if ($addressData['region_id']) {
                    $regionId = $addressData['region_id'];
                    $region = $this->getRegionById($regionId);
                    $regionCode = $region->getCode();
                    $countryRegionArr[$countryId][$regionCode] =
                        strtoupper($countryId).
                        '-'.
                        strtoupper($regionCode);

                    if (!isset($countrySaleArr[$countryId][$regionCode])) {
                        $countrySaleArr[$countryId][$regionCode] =
                        $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$regionCode] = 1;
                    } else {
                        $countrySaleArr[$countryId][$regionCode] =
                            $countrySaleArr[$countryId][$regionCode] +
                            $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$regionCode] =
                            $countryOrderCountArr[$countryId][$regionCode] +
                            1;
                    }
                } else {
                    $countryRegionArr[$countryId][$countryId] =
                    strtoupper($countryId);
                    if (!isset($countrySaleArr[$countryId][$countryId])) {
                        $countrySaleArr[$countryId][$countryId] =
                        $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$countryId] = 1;
                    } else {
                        $countrySaleArr[$countryId][$countryId] =
                            $countrySaleArr[$countryId][$countryId] +
                            $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$countryId] =
                            $countryOrderCountArr[$countryId][$countryId] +
                            1;
                    }
                }
            }
        }
        $data['country_arr'] = $countryArr;
        $data['country_sale_arr'] = $countrySaleArr;
        $data['country_order_count_arr'] = $countryOrderCountArr;
        $data['country_region_arr'] = $countryRegionArr;
        return $data;
    }

    // get best customer collection
    public function getCustomerCollection($data)
    {
        $productQuantityCollection = [];
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerIdFlag = 0;
        if ($sellerId!='') {
            $sellerIdFlag = 1;
        }
        $sellerOrderCollection = $this->getProductCollectionByFilter($data);
        $orderSaleArr = [];
        $orderIds = [];
        $returnData = [];
        $customerCount = $this->getCustomerCountSetting();
        $lastPurchase = [];

        $marketplaceSalesListTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('marketplace_saleslist');

        $customerEntityTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('customer_grid_flat');

        $salesCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToSelect(
                [
                    'customer_id',
                    'entity_id',
                    'created_at'
                ]
            )
            ->setOrder('created_at', 'DESC');

        foreach ($salesCollection as $salesOrder) {
            if (!array_key_exists(
                $salesOrder->getCustomerId(),
                $lastPurchase
            )) {
                $lastPurchase[$salesOrder->getCustomerId()] =
                $salesOrder->getCreatedAt();
            }
        }

        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToSelect(['customer_id','entity_id']);

        $orderCollection->getSelect()
            ->join(
                $customerEntityTable.' as customer',
                'main_table.customer_id = customer.entity_id',
                [
                    'customer_name'=>'name',
                    'registration_date'=>'created_at'
                ]
            );
        if ($sellerIdFlag) {
            $orderCollection->getSelect()
                ->join(
                    $marketplaceSalesListTable.' as mpsaleslist',
                    ' main_table.entity_id = mpsaleslist.order_id',
                    [
                        'mpsaleslist.actual_seller_amount',
                        'mpsaleslist.created_at'
                    ]
                )
                ->where(
                    'mpsaleslist.seller_id = '.$sellerId
                );
            $orderCollection->getSelect()
            ->columns('SUM(actual_seller_amount + total_tax) AS seller_amount')
            ->columns('count(DISTINCT order_id) AS total_order')
            ->group('main_table.customer_id');
        } else {
            $orderCollection->getSelect()
                ->join(
                    $marketplaceSalesListTable.' as mpsaleslist',
                    ' main_table.entity_id = mpsaleslist.order_id',
                    [
                        'mpsaleslist.actual_seller_amount',
                        'mpsaleslist.total_commission',
                        'mpsaleslist.created_at'
                    ]
                );
            $orderCollection->getSelect()
            ->columns('SUM(actual_seller_amount + total_commission + total_tax) AS seller_amount')
            ->columns('count(DISTINCT order_id) AS total_order')
            ->group('main_table.customer_id');
        }

        $orderCollection->setOrder('seller_amount', 'DESC');
        $orderCollection->setPageSize($customerCount)
            ->setCurPage(1);

        $returnData = $this->getCustomerArrayData(
            $orderCollection,
            $lastPurchase
        );
        return $returnData;
    }

    // convert customer collection data to an array
    public function getCustomerArrayData($orderCollection, $lastPurchase)
    {
        $customerIds = [];
        $customerOrderCount = [];
        $customerTotalSale = [];
        $customerIdArray = [];
        $data = [];
        foreach ($orderCollection as $order) {
            $orderData = $order->getData();
            $tempArray = [];
            $tempArray['refused'] = $this->getRefusedOrder(['canceled','closed'], $orderData['customer_id']);
            $tempArray['totalSale'] = $orderData['seller_amount'];
            $tempArray['totalcount'] = $orderData['total_order'];
            $lastdate = $this->_storeTime->date(new \DateTime($lastPurchase[$orderData['customer_id']]));
            $tempArray['lastpurchase'] = $lastdate->format('Y-m-d H:i:s');
            $tempArray['customer_name'] = $orderData['customer_name'];
            $date = $this->_storeTime->date(new \DateTime($orderData['registration_date']));
            $tempArray['registration_date'] = $date->format('Y-m-d H:i:s');
            $data[$order->getCustomerId()] = $tempArray;
        }
        return $data;
    }
    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }
    // get base currency symbol
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    // get sales collection data for sales graph
    public function getSalesAmount($data)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $totalSales = 0;
        if (array_key_exists('filter', $data)) {
            if ($data['filter']=='year') {
                $returnData = $this->getYearlySale($sellerId, $data);
            } elseif ($data['filter']=='month') {
                $returnData = $this->getMonthSale($sellerId, $data);
            } elseif ($data['filter']=='week') {
                $returnData = $this->getWeeklySale($sellerId, $data);
            } elseif ($data['filter']=='day') {
                $returnData = $this->getDailySale($sellerId, $data);
            }
        } else {
            $returnData = $this->getYearlySale($sellerId, $data);
        }

        return $returnData;
    }

    // get year sales data according to year fiter
    public function getYearlySale($sellerId, $paramData)
    {
        $totalSaleAmount = 0;
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $monthsArr = [
            '',
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
        for ($i = 1; $i <= $currMonth; ++$i) {
            $date1 = $curryear.'-'.$i.'-01 00:00:00';
            $date2 = $curryear.'-'.$i.'-31 23:59:59';
            $collection = $this->getSalesListCollection($sellerId, $paramData);
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            
            $totalSale = 0;
            foreach ($collection as $record) {
                $totalSale = $totalSale + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $totalSaleAmount = $totalSaleAmount + $totalSale;
            $data['values'][$i] = $this->getCurrentAmount($totalSale);
            if ($i != $currMonth) {
                $data['chxl'] = $data['chxl'].$monthsArr[$i].'|';
            } else {
                $data['chxl'] = $data['chxl'].$monthsArr[$i];
            }
            $data['totalsale'] = $totalSaleAmount;
        }
        return $data;
    }

    // get month sales data according to month filter
    public function getMonthSale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        for ($i = 1; $i <= $currDays; ++$i) {
            $date1 = $curryear.'-'.$currMonth.'-'.$i.' 00:00:00';
            $date2 = $curryear.'-'.$currMonth.'-'.$i.' 23:59:59';
            $salesCollection = $this->getSalesListCollection(
                $sellerId,
                $paramData
            );
            $salesCollection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            $sum = [];
            $totalSales = 0;
            foreach ($salesCollection as $record) {
                $totalSales = $totalSales + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $price = $totalSales;
            if ($price * 1 && $i != $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.
                $i.'/'.
                $curryear.'|';
            } elseif ($i < 5 && $price * 1 == 0 && $i != $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.$i.'/'.
                $curryear.'|';
            }
            if ($i == $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] =
                $data['chxl'].
                $currMonth.'/'.
                $i.'/'.$curryear;
            }
        }
        return $data;
    }

    // get weekly sales data according to weekly sales
    public function getWeeklySale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currWeekDay = date('N');
        $currWeekStartDay = $currDays - $currWeekDay;
        $currWeekEndDay = $currWeekStartDay + 7;
        $currentDayOfMonth=date('j');
        if ($currWeekEndDay > $currentDayOfMonth) {
            $currWeekEndDay = $currentDayOfMonth;
        }
        for ($i = $currWeekStartDay + 1; $i <= $currWeekEndDay; ++$i) {
            $date1 = $curryear.'-'.$currMonth.'-'.$i.' 00:00:00';
            $date2 = $curryear.'-'.$currMonth.'-'.$i.' 23:59:59';
            $collection = $this->getSalesListCollection(
                $sellerId,
                $paramData
            );
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            $sum = [];
            $temp = 0;
            foreach ($collection as $record) {
                $temp = $temp + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $price = $temp;
            if ($i != $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.$i.
                '/'.$curryear.'|';
            }
            if ($i == $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.
                $i.'/'.$curryear;
            }
        }
        return $data;
    }

    // get currenct day sales collection
    public function getDailySale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currTime = date('G');
        $arr = [];
        $k = 0;
        for ($i = 0; $i <= 23; $i++) {
            $date1 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$i.':00:00';
            $updatedDate1 = $this->_storeTime->convertConfigTimeToUtc($date1);

            $j = $i+2;
            $date2 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$j.':59:59';
            $updatedDate2 = $this->_storeTime->convertConfigTimeToUtc($date2);
            $collection = $this->getSalesListCollection($sellerId, $paramData);
            $collection->addFieldToFilter(
                'created_at',
                [
                    'datetime' => true,
                    'from' => $updatedDate1,
                    'to' => $updatedDate2
                ]
            );
            
            $sum = [];
            $totalSales = 0;
            foreach ($collection as $record) {
                $totalSales = $totalSales + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $price = $totalSales;
            if ($i != 23) {
                $data['values'][$k] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                date("g:i A", strtotime($date2)).'|';
            }
            if ($i == 23) {
                $data['values'][$k] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].date(
                    "g:1 A",
                    strtotime($date2)
                );
            }
            $i = $j;
            $k++;
        }
        return $data;
    }

    // get all the order ids of selected order status
    public function getOrderIdsByOrderStatus($orderStatus)
    {
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['in'  =>  $orderStatus]);
        $orderIds = $this->getFieldArrayFromCollection(
            $orderCollection,
            'entity_id'
        );
        return $orderIds;
    }

    public function getRefusedOrder($status, $customerId)
    {
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['in'  =>  $status])
            ->addFieldToFilter('customer_id', $customerId);
        return count($orderCollection);
    }

    // get collection data for particular field
    public function getFieldArrayFromCollection($collection, $field)
    {
        $fieldArray = [];
        $collectionData = $collection->getData();
        foreach ($collectionData as $value) {
            $fieldArray[] = $value[$field];
        }
        return $fieldArray;
    }

    // get encryoted hash data for graph security
    public function getChartEncryptedHashData($data)
    {
        return md5($data . $this->_deploymentConfigDate);
    }

    // get sales collection data
    public function getSalesCollection($paramData)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $paramData)) {
            $sellerId = $paramData['seller_id'];
        }
        $dateFilter = 0;
        if (array_key_exists(
            'wk_report_date_start',
            $paramData
        ) && array_key_exists(
            'wk_report_date_end',
            $paramData
        )) {
            if ($paramData['wk_report_date_start']!='' &&
                $paramData['wk_report_date_end']!=''
            ) {
                $dateFrom = $paramData['wk_report_date_start'];
                $dateTo = $paramData['wk_report_date_end'];
                $dateFilter = 1;
            }
        }
        $collection = $this->getSalesListCollection(
            $sellerId,
            $paramData
        );
        if ($dateFilter) {
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $dateFrom, 'to' => $dateTo]
            );
        }
        $collection->addFieldToSelect('magequantity')
            ->addFieldToSelect('actual_seller_amount')
            ->addFieldToSelect('created_at')
            ->setOrder('created_at', 'DESC');
        $collection->getSelect()
            ->columns('SUM(magequantity) AS total_item_qty')
            ->columns('SUM(actual_seller_amount + total_tax) AS total_seller_amount')
            ->columns('COUNT(DISTINCT order_id) AS total_order_id')
            ->columns('DATE_FORMAT(created_at, "%y-%m-%d") AS order_date')
            ->group('DATE_FORMAT(created_at, "%y-%m-%d")');

        return $collection;
    }

    // get customer count seeting from system config
    public function getCustomerCountSetting()
    {
        return $this->scopeConfig->getValue(
            'wk_mpreportsystem/general_settings/customerdatacount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get product count settings from system config
    public function gettopPtoductCountSetting()
    {
        return $this->scopeConfig->getValue(
            'wk_mpreportsystem/general_settings/productdatacount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get saleslist collection according to filer
    public function getSalesListCollection($sellerId, $data)
    {
        $sellerIdFlag = 0;
        if ($sellerId != '') {
            $sellerIdFlag = 1;
        }
        $productIds = [];
        $categoryFlag = 0;
        $orderIds = [];
        $orderFlag = 0;
        if (array_key_exists('categories', $data) &&
            is_array($data['categories'])
        ) {
            $productIds = $this->getProductIdsByCategoryIds(
                $data['categories']
            );
            $categoryFlag = 1;
        }
        if (array_key_exists(
            'orderstatus',
            $data
        ) && is_array(
            $data['orderstatus']
        )) {
            $orderIds = $this->getOrderIdsByOrderStatus($data['orderstatus']);
            $orderFlag = 1;
        }
        $collection = $this->_marketplacesaleslist
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['neq' => 0]
            );
        if ($sellerIdFlag) {
            $collection->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        }
        if ($categoryFlag) {
            $collection->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIds]
            );
        }
        if ($orderFlag) {
            $collection->addFieldToFilter(
                'order_id',
                ['in' => $orderIds]
            );
        } else {
            $orderIds = $this->getOrderIdsByOrderStatus(['canceled']);
            if (!empty($orderIds)) {
                $collection->addFieldToFilter(
                    'order_id',
                    ['nin' => $orderIds]
                );
            }
        }
        return $collection;
    }

    public function getRegionById($regionId)
    {
        $regionModel = $this->_regionFactory->create()->load($regionId);
        return $regionModel;
    }
    public function getCurrentAmount($amount)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore()->getStoreId();
        return $priceCurrencyObject->convert($amount, $store, $currency);
    }
    public function convertPrice($amount = 0, $store = null, $currency = null)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        if ($store == null) {
            $store = $storeManager->getStore()->getStoreId();
        }
        $rate = $priceCurrencyObject->convertAndFormat($amount, $includeContainer = true, $precision = 2, $store, $currency);
        return $rate;
    }
}
