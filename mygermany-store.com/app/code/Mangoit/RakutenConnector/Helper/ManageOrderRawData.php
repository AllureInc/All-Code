<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Helper;

use Mangoit\RakutenConnector\Model\AmazonTempData;
use Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Mangoit\RakutenConnector\Api\OrderMapRepositoryInterface;
use Mangoit\RakutenConnector\Helper\ManageProductRawData;
use Mangoit\RakutenConnector\Model\OrderMap;
use Mangoit\RakutenConnector\Model\Accounts;

class ManageOrderRawData extends \Magento\Framework\App\Helper\AbstractHelper
{
    /*
    contain Rakuten Client
    */
    public $rktnClient;

    /**
     * @var AmazonTempData
     */
    private $rakutenTempData;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Mangoit\RakutenConnector\Logger\Logger
     */
    private $logger;

    /**
     * @var ProductMapRepositoryInterface
     */
    private $productMapRepo;

    /**
     * @var OrderMapRepositoryInterface
     */
    private $orderMapRepo;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $helper
     * @param \Mangoit\RakutenConnector\Helper\Order $orderHelper
     * @param AmazonTempData $rakutenTempData
     * @param AmazonTempDataRepositoryInterface $rakutenTempDataRepository
     * @param \Mangoit\RakutenConnector\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param OrderMapRepositoryInterface $orderMapRepo
     * @param ManageProductRawData $manageProductRawData
     * @param OrderMap $orderMapRecord
     * @param Accounts $accounts
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Directory\Model\Region $region
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $helper,
        \Mangoit\RakutenConnector\Helper\Order $orderHelper,
        AmazonTempData $rakutenTempData,
        AmazonTempDataRepositoryInterface $rakutenTempDataRepository,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        OrderMapRepositoryInterface $orderMapRepo,
        ManageProductRawData $manageProductRawData,
        OrderMap $orderMapRecord,
        Accounts $accounts,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\Region $region
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->rakutenTempData = $rakutenTempData;
        $this->rakutenTempDataRepository = $rakutenTempDataRepository;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->orderMapRepo = $orderMapRepo;
        $this->orderHelper = $orderHelper;
        $this->manageProductRawData = $manageProductRawData;
        $this->orderMapRecord = $orderMapRecord;
        $this->accounts = $accounts;
        $this->backendSession = $backendSession;
        $this->productRepository = $productRepository;
        $this->region = $region;
    }

    /**
     * get amazon order between ranage
     *
     * @param array $rangeData
     * @param boolean $viaCron
     * @return void | array
     */
    public function getFinalOrderReport($rangeData, $viaCron = false)
    {
            $orderParams = [];
            $notifications = null;
            $error = false;
            $errorData = '';
            $errorMsg = null;
            $response = [];
            $orderNextToken = $rangeData['next_token'];
            $orderParams['recordCount'] = '20';
            $toDate = new \DateTime($rangeData['order_to']);

            $fromDate = new \DateTime($rangeData['order_from']);

        try {
            $rktnOrders = '';
            $count = 0;
            if (!empty($orderNextToken)) {
                $orderLists = $this->rktnClient->listOrdersByNextToken($orderNextToken);
                if (isset($orderLists['ListOrdersByNextTokenResult']['NextToken'])) {
                    $this->backendSession
                        ->setData(
                            'order_next_token',
                            $orderLists['ListOrdersByNextTokenResult']['NextToken']
                        );
                } else {
                    $orderNextToken = '';
                }

                if (isset($orderLists['ListOrdersByNextTokenResult']['Orders']['Order'])) {
                    $ordersArray = $orderLists['ListOrdersByNextTokenResult']['Orders']['Order'];
                    $rktnOrders = isset($ordersArray[0])? $ordersArray : [$ordersArray];
                }
            } else {
                $orderLists = $this->rktnClient
                        ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
                    
                // if (isset($orderLists['ListOrdersResult']['NextToken'])) {
                //     $orderNextToken = $orderLists['ListOrdersResult']['NextToken'];
                // }

                if (isset($orderLists['orders']['order'])) {
                    $orderListArr = $orderLists['orders']['order'];
                    $rktnOrders = isset($orderListArr[0]) ? $orderListArr : [$orderListArr];
                }
            }

            if (!empty($rktnOrders)) {
                $notifications = $this->manageOrderData($rktnOrders, $this->helper->getCustomerId(), $viaCron);

                $errorMsg = $errorMsg.$notifications['notification'];
                $errorData = $errorData.$notifications['errorMsg'];
                $count = $count + count($notifications['items']);
            } else {
                if (isset($orderLists['error'])) {
                    $error = true;
                    $errorMsg = $orderLists['error'];
                } else {
                    $error = true;
                    $errorMsg = __('No Rakuten Order(s) Found');
                }
            }
                
            $response['error_msg'] = $errorData;
            $response['next_token'] = $orderNextToken;
            $response['notification'] = $errorMsg;
            $response['data'] = $count;
        } catch (\Exception $e) {
            $response['notification'] = '';
            $response['data'] = '';
            $response['error'] = true;
            $response['error_msg'] = $e->getMessage();
            $response['next_token'] = '';
        }
            return $response;
    }

    /**
     * manage order data
     * @param  array  $rktnOrders
     * @param  boolean $sellerId
     * @param  boolean $viaCron
     * @return array
     */
    public function manageOrderData($rktnOrders, $sellerId = false, $viaCron = false)
    {
        $items = [];
        $notifications = [];
        $errorMsg = '';
        $i=0;
        
        try {
            $streetLineCount = $this->helper->getStreetLineNumber();

            $tempAvlImported = $this->rakutenTempData->getCollection()
                ->addFieldToFilter('item_type', 'order')
                ->getColumnValues('item_id');

            $alreadyMapped = $this->orderMapRecord
                            ->getCollection()
                            ->getColumnValues('rakuten_order_id');
            $tempAvlImported =  array_merge($tempAvlImported, $alreadyMapped);
            // $validOrderStatus = ['Shipped','PartiallyShipped','Unshipped'];
            $validOrderStatus = ['pending','editable','shipped','payout','canceled'];

            foreach ($rktnOrders as $key => $rawOrderData) {
                if (in_array($rawOrderData['order_no'], $tempAvlImported)) {
                    continue;
                }
                
                if (!in_array($rawOrderData['status'], $validOrderStatus)) {
                    continue;
                }
                
                /****/
                $firstName = 'Guest';
                $lastName = 'User';
                $shipPrice = 0;
                $shipMethod = __('From Rakuten ');

                // if (isset($rawOrderData['ShipServiceLevel'])) {
                //     $shipMethod .= $rawOrderData['ShipServiceLevel'];
                // }
                if (!isset($rawOrderData['delivery_address']) || !isset($rawOrderData['client']['email'])) {
                    continue;
                }

                // $orderCmptDetails = $this->getExtraDetailsOfAmzOrder(
                //     $rawOrderData['order_no'],
                //     $sellerId,
                //     $viaCron
                // );
                $orderCmptDetails = $this->getItemsOfRakutenOrder(
                    $rawOrderData['items'],
                    $sellerId,
                    $viaCron
                );
                if (empty($orderCmptDetails)) {
                    continue;
                }
                // if (!isset($rawOrderData['items'])) {
                //     continue;
                // }
                // $orderCmptDetails = $rawOrderData['items'];

                $orderItems = [];
                $shippingCharge = $rawOrderData['shipping'];
                $invalidOrder = false;
                foreach ($orderCmptDetails as $key => $rktnOrderItem) {
                    if (isset($rktnOrderItem['error_msg']) && !$rktnOrderItem['error_msg']) {
                        $productId = $rktnOrderItem['orderItems']['product_id'];
                        
                        if ($productId) {
                            $orderItems[] = [
                                        'product_id' => $rktnOrderItem['orderItems']['product_id'],
                                        'qty' => $rktnOrderItem['orderItems']['qty'],
                                        'price' => $rktnOrderItem['orderItems']['price'],
                                    ];
                            // $shippingCharge = $shippingCharge +  $amzorderItem['shipping_price']['price'];
                        } else {
                            $errorMsg = $errorMsg.' Rakuten order no. : <b>'
                                            .$rawOrderData['order_no']
                                            ."</b> not sync because Product <b>'"
                                            .$rktnOrderItem['title']
                                            .$rktnOrderItem['product_id']
                                            ."'</b> not exist on your Rakuten <br />";
                            $this->logger->info($errorMsg);
                            $invalidOrder = true;
                        }
                    } else {
                        $this->logger->info(json_decode($orderCmptDetails));
                        $invalidOrder = true;
                    }
                }
                // foreach ($orderCmptDetails as $key => $amzorderItem) {
                //     if (isset($amzorderItem['error_msg']) && !$amzorderItem['error_msg']) {
                //         $productId = $amzorderItem['orderItems']['product_id'];
                        
                //         if ($productId) {
                //             $orderItems[] = [
                //                         'product_id' => $amzorderItem['orderItems']['product_id'],
                //                         'qty' => $amzorderItem['orderItems']['qty'],
                //                         'price' => $amzorderItem['orderItems']['price'],
                //                     ];
                //             $shippingCharge = $shippingCharge +  $amzorderItem['shipping_price']['price'];
                //         } else {
                //             $errorMsg = $errorMsg.' Rakuten order id : <b>'
                //                             .$rawOrderData['AmazonOrderId']
                //                             ."</b> not sync because Product <b>'"
                //                             .$amzorderItem['title']
                //                             .$amzorderItem['productAsin']
                //                             ."'</b> not exist on your amazon <br />";
                //             $this->logger->info($errorMsg);
                //             $invalidOrder = true;
                //         }
                //     } else {
                //         $this->logger->info(json_decode($orderCmptDetails));
                //         $invalidOrder = true;
                //     }
                // }

                if ($invalidOrder) {
                    continue;
                }

                if (isset($rawOrderData['delivery_address']['first_name'])) {
                    // $buyerData = explode(" ", trim($rawOrderData['ShippingAddress']['Name']), 2);

                    $firstName = $rawOrderData['delivery_address']['first_name'];
                    $lastName = isset($rawOrderData['delivery_address']['last_name']) ?
                        $rawOrderData['delivery_address']['last_name'] : '';
                }
                /****/
                foreach ($rawOrderData['delivery_address'] as $key => $value) {
                    if ($value == '') {
                        $rawOrderData['delivery_address'][$key] = __('NA');
                        $rawOrderData['delivery_address']['save_in_address_book'] = 0;
                    }
                }
                if (!isset($rawOrderData['delivery_address']['country'])) {
                    $rawOrderData['delivery_address']['country'] = __('NA');
                }

                $streetAddress = '';
                $addressLine1 = isset($rawOrderData['delivery_address']['street'])? $rawOrderData['delivery_address']['street'] : '';
                $addressLine2 = isset($rawOrderData['delivery_address']['address_add'])? $rawOrderData['delivery_address']['address_add'] : '';
                
                if (!isset($rawOrderData['delivery_address']['address_add'])) {
                    $streetAddress = $addressLine1;
                } else {
                    if ($streetLineCount > 1) {
                        $addressJoin = !empty($addressLine1) ? $addressLine1."\r\n" : '';
                        $streetAddress = $addressJoin . $addressLine2;
                    } else {
                        $streetAddress = isset($addressLine1) ? $addressLine1 : $addressLine2;
                    }
                }

                $region = $this->getOrderRegion($rawOrderData);

                $tempOrder = [
                    'rktn_order_id' => $rawOrderData['order_no'],
                    'order_status' => $rawOrderData['status'],
                    'currency_id' => 'EUR',//$rawOrderData['OrderTotal']['CurrencyCode'],
                    'purchase_date' => $rawOrderData['created'],
                    'email' => $rawOrderData['client']['email'],
                    'shipping_address' => [
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'street' => $streetAddress,
                        'city' => $rawOrderData['delivery_address']['city'],
                        'country_id' => $rawOrderData['delivery_address']['country'],
                        'region' => $region,
                        'postcode' => $rawOrderData['delivery_address']['zip_code'],
                        'telephone' => isset($rawOrderData['client']['phone']) ? $rawOrderData['client']['phone'] : '0000',
                        'fax' => '',
                        'vat_id' => '',
                        'save_in_address_book' => 1,
                    ],
                    'items' => $orderItems,
                    'shipping_service' => ['method' => $shipMethod,'cost' => $shippingCharge],
                ];
                // print_r($tempOrder);
                if (!$viaCron) {
                    $dt = new \DateTime();
                    $currentDate = $dt->format('Y-m-d\TH:i:s');

                    $tempdata = [
                            'item_type' => 'order',
                            'item_id' => $tempOrder['rktn_order_id'],
                            'item_data' => json_encode($tempOrder),
                            'created_at' => $currentDate,
                            'seller_id' => $sellerId,
                            'purchase_date' => $rawOrderData['created']
                        ];
                    // print_r($tempdata);
                    // die;
                    $tempOrderData = $this->rakutenTempData;
                    $tempOrderData->setData($tempdata);
                    $item = $tempOrderData->save();
                    array_push($items, $item->getEntityId());
                } else {
                    $mapedOrder = $this->orderMapRepo
                            ->getByRktnOrderId($rawOrderData['order_no'])
                            ->getFirstItem();
                    if ($mapedOrder->getEntityId()) {
                        continue;
                    }
                    $date = new \DateTime($rawOrderData['created']);
                    $purchaseDate = $date->format('Y-m-d H:i:s');

                    $orderData = $this->orderHelper
                            ->createRakutenOrderAtMage($tempOrder);

                    $this->logger->info(' Order created At Magento ');
                    $this->logger->info(json_encode($orderData));

                    $items[$i]['order'] = $orderData;

                    if (isset($orderData['order_id']) && $orderData['order_id']) {
                        $data = [
                                'rakuten_order_id' => $tempOrder['rktn_order_id'],
                                'mage_order_id' => $orderData['order_id'],
                                'status' => $tempOrder['order_status'],
                                'seller_id'   => $sellerId,
                                'purchase_date' => $purchaseDate
                              ];

                        $record = $this->orderMapRecord;
                        $record->setData($data)->save();
                    }
                    $i++;
                }
            }
            $notifications['errorMsg'] = false;
            $notifications['notification'] = $errorMsg;
            $notifications['items'] = $items;
        } catch (\Exception $e) {
            $notifications['notification'] = '';
            $notifications['items'] = '';
            $notifications['errorMsg'] = $e->getMessage();
        }
        return $notifications;
    }

    /**
     * get region code by amazon order raw data
     *
     * @param array $amzOrderRawData
     * @return string
     */
    public function getOrderRegion($rawOrderData)
    {
        $region = isset($rawOrderData['delivery_address']['StateOrRegion']) ? $rawOrderData['delivery_address']['StateOrRegion'] : $rawOrderData['delivery_address']['city'];
        $addState = [];
        $requiredStates = $this->helper->getRequiredStateList();
        $requiredStatesArray = explode(',', $requiredStates);
        if (in_array($rawOrderData['delivery_address']['country'], $requiredStatesArray)) {
            $countryId = $rawOrderData['delivery_address']['country'];

            $regionData = $this->region->loadByName($region, $countryId);
            
            if ($regionData->getRegionId()) {
                $region = $regionData->getRegionId();
            } else {
                $regionData = $this->region->loadByName('other', $countryId);
                if ($regionData->getRegionId()) {
                    $region = $regionData->getRegionId();
                } else {
                    $addState['country_id'] = $countryId;
                    $addState['code'] = 'other';
                    $addState['default_name'] = 'other';
                    $region = $this->region->setData($addState)->save()->getRegionId();
                }
            }
        }
        return $region;
    }

    /**
     * get complete details of order linke shipping, item cost, and product.
     * @param  string $AmazonOrderId
     * @return array
     */
    public function getItemsOfRakutenOrder($rktnOrderItems, $sellerId, $viaCron)
    {
        // $rktnOrderItems = $this->rktnClient->listOrderItems($rakutenOrderId);
        $orderItems = [];
        if (count($rktnOrderItems)) {
            foreach ($rktnOrderItems as $key => $rktnOrderItem) {
                $rktnOrderDetails = [];
                $errorMsg = true;
                if (isset($rktnOrderItem['product_id'])) {
                    if (!isset($rktnOrderItem['price'])) {
                        continue;
                    }
                    $rktnProCollection =  $this->productMapRepo
                            ->getByRktProductId($rktnOrderItem['product_id'])->getFirstItem();
                            // print_r($rktnProCollection->getData());die;
                    $rktnOrderDetails['product_id'] = $rktnOrderItem['product_id'];
                    $rktnOrderDetails['title'] = $rktnOrderItem['name'];
                    if ($rktnProCollection->getEntityId()) {
                        $errorMsg = false;
                        $productId = null;
                        $productType = null;
                        $productId = $rktnProCollection->getMagentoProId();
                        $productType = $rktnProCollection->getProductType();
                        $rktnOrderDetails['orderItems'] = [
                            'product_id' => $productId,
                            'product_type' => $productType,
                            'qty' => $rktnOrderItem['qty'],
                            'price' => $rktnOrderItem['price']/$rktnOrderItem['qty'],
                            'curreny_code' => 'EUR'/*$rktnOrderItem['ItemPrice']['CurrencyCode']*/,
                        ];
                        $rktnOrderDetails['error_msg'] = $errorMsg;
                    } else {
                        $result = $this->manageProductRawData->getProductByRktnProId($rktnOrderItem['product_id'], false, true);

                        if (!empty($result['error'])) {
                            $product = $this->productRepository->get($result['sku']);
                            if ($product->getId()) {
                                $errorMsg = false;
                                $rktnOrderDetails['orderItems'] = [
                                    'product_id' => $product->getId(),
                                    'product_type' => $product->getTypeId(),
                                    'qty' => $rktnOrderItem['qty'],
                                    'price' => $rktnOrderItem['price']/$rktnOrderItem['qty'],
                                    'curreny_code' => 'EUR'/*$rktnOrderItem['ItemPrice']['CurrencyCode']*/,
                                ];
                                $rktnOrderDetails['error_msg'] = $errorMsg;
                            } else {
                                $errorMsg = true;
                                $rktnOrderDetails['error_msg'] = $errorMsg;

                                $this->logger->info(' order Product Item Not found in Rakuten!. Details are  ');
                                $this->logger->info(json_encode($rktnOrderDetails));
                            }
                        } else {
                            $rktnProCollection =  $this->productMapRepo
                                ->getByRktProductId($rktnOrderItem['ASIN'])->getFirstItem();
                            $errorMsg = false;

                            $rktnOrderDetails['orderItems'] = [
                                'product_id' => $rktnProCollection->getMagentoProId(),
                                'product_type' => $rktnProCollection->getProductType(),
                                'qty' => $rktnOrderItem['QuantityOrdered'],
                                'price' => $rktnOrderItem['ItemPrice']['Amount']/$rktnOrderItem['QuantityOrdered'],
                                'curreny_code' => $rktnOrderItem['ItemPrice']['CurrencyCode'],
                            ];
                            $rktnOrderDetails['error_msg'] = $errorMsg;
                        }
                    }
                    // if (!$errorMsg) {
                    //     if (isset($rktnOrderItem['ShippingPrice'])) {
                    //         $rktnOrderDetails['shipping_price'] = [
                    //             'price' => $rktnOrderItem['ShippingPrice']['Amount'],
                    //             'curreny_code' => $rktnOrderItem['ShippingPrice']['CurrencyCode'],
                    //         ];
                    //     } else {
                    //         $rktnOrderDetails['shipping_price'] = [
                    //             'price' => '0',
                    //             'curreny_code' => $this->helper->getAmazonCurrencyCode(),
                    //         ];
                    //     }
                    // }
                } else {
                    $rktnOrderDetails['error_msg'] = $errorMsg;
                }
                $orderItems[] = $rktnOrderDetails;
            }
        }
        return $orderItems;
    }
    /**
     * get complete details of order linke shipping, item cost, and product.
     * @param  string $AmazonOrderId
     * @return array
     */
    // public function getExtraDetailsOfAmzOrder($rakutenOrderId, $sellerId, $viaCron)
    // {
    //     $amzOrderItems = $this->rktnClient->listOrderItems($rakutenOrderId);
    //     $orderItems = [];
    //     if (count($amzOrderItems)) {
    //         foreach ($amzOrderItems as $key => $amzOrder) {
    //             $amzOrderDetails = [];
    //             $errorMsg = true;
    //             if (isset($amzOrder['ASIN'])) {
    //                 if (!isset($amzOrder['ItemPrice'])) {
    //                     continue;
    //                 }
    //                 $amzProCollection =  $this->productMapRepo
    //                         ->getByRktProductId($amzOrder['ASIN'])->getFirstItem();
    //                 $amzOrderDetails['productAsin'] = $amzOrder['ASIN'];
    //                 $amzOrderDetails['title'] = $amzOrder['Title'];
    //                 if ($amzProCollection->getEntityId()) {
    //                     $errorMsg = false;
    //                     $productId = null;
    //                     $productType = null;
    //                     $productId = $amzProCollection->getMagentoProId();
    //                     $productType = $amzProCollection->getProductType();
    //                     $amzOrderDetails['orderItems'] = [
    //                         'product_id' => $productId,
    //                         'product_type' => $productType,
    //                         'qty' => $amzOrder['QuantityOrdered'],
    //                         'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
    //                         'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
    //                     ];
    //                     $amzOrderDetails['error_msg'] = $errorMsg;
    //                 } else {
    //                     $result = $this->manageProductRawData->getProductByAsin($amzOrder['ASIN'], false, true);

    //                     if (!empty($result['error'])) {
    //                         $product = $this->productRepository->get($result['sku']);
    //                         if ($product->getId()) {
    //                             $errorMsg = false;
    //                             $amzOrderDetails['orderItems'] = [
    //                                 'product_id' => $product->getId(),
    //                                 'product_type' => $product->getTypeId(),
    //                                 'qty' => $amzOrder['QuantityOrdered'],
    //                                 'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
    //                                 'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
    //                             ];
    //                             $amzOrderDetails['error_msg'] = $errorMsg;
    //                         } else {
    //                             $errorMsg = true;
    //                             $amzOrderDetails['error_msg'] = $errorMsg;

    //                             $this->logger->info(' order Product Item Not found in amazon!. Details are  ');
    //                             $this->logger->info(json_encode($amzOrderDetails));
    //                         }
    //                     } else {
    //                         $amzProCollection =  $this->productMapRepo
    //                             ->getByRktProductId($amzOrder['ASIN'])->getFirstItem();
    //                         $errorMsg = false;

    //                         $amzOrderDetails['orderItems'] = [
    //                             'product_id' => $amzProCollection->getMagentoProId(),
    //                             'product_type' => $amzProCollection->getProductType(),
    //                             'qty' => $amzOrder['QuantityOrdered'],
    //                             'price' => $amzOrder['ItemPrice']['Amount']/$amzOrder['QuantityOrdered'],
    //                             'curreny_code' => $amzOrder['ItemPrice']['CurrencyCode'],
    //                         ];
    //                         $amzOrderDetails['error_msg'] = $errorMsg;
    //                     }
    //                 }
    //                 if (!$errorMsg) {
    //                     if (isset($amzOrder['ShippingPrice'])) {
    //                         $amzOrderDetails['shipping_price'] = [
    //                             'price' => $amzOrder['ShippingPrice']['Amount'],
    //                             'curreny_code' => $amzOrder['ShippingPrice']['CurrencyCode'],
    //                         ];
    //                     } else {
    //                         $amzOrderDetails['shipping_price'] = [
    //                             'price' => '0',
    //                             'curreny_code' => $this->helper->getAmazonCurrencyCode(),
    //                         ];
    //                     }
    //                 }
    //             } else {
    //                 $amzOrderDetails['error_msg'] = $errorMsg;
    //             }
    //             $orderItems[] = $amzOrderDetails;
    //         }
    //     }
    //     return $orderItems;
    // }
}
