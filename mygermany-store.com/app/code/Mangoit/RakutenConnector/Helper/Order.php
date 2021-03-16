<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\OrdersFactory;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $rktnClient;
    
    /*
    StoreManagerInterface
     */
    private $storeManager;

    /*
    \Magento\Catalog\Model\Product
     */
    private $product;

    /*
    StockRegistryInterface
     */
    private $stockRegistry;

    /*
    \Mangoit\RakutenConnector\Logger\Logger
     */
    private $logger;

    /*
    \Magento\Sales\Model\Order
     */
    private $order;

    /*
    \Magento\Quote\Model\Quote\Address\Rate
     */
    private $shippingRate;

    /*
    \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;

    /*
    \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;

    /*
    \Magento\Backend\Model\Session
     */
    private $backendSession;

    /*
    \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /*
    \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /*
    \Mangoit\RakutenConnector\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\App\Helper\Context             $context
     * @param StoreManagerInterface                             $storeManager
     * @param \Magento\Catalog\Model\Product                    $product
     * @param StockRegistryInterface                            $stockRegistry
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Backend\Model\Session                    $backendSession
     * @param \Mangoit\RakutenConnector\Logger\Logger        $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface        $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface        $cartManagementInterface
     * @param \Magento\Quote\Model\Quote\Address\Rate           $shippingRate
     * @param \Magento\Sales\Model\Order                        $order
     * @param \Mangoit\RakutenConnector\Helper\Data          $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Backend\Model\Session $backendSession,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Sales\Model\Order $order,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        SaleslistFactory $mpSaleslist,
        OrdersFactory $mpOrders
    ) {
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->backendSession = $backendSession;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->shippingRate = $shippingRate;
        $this->order = $order;
        $this->helper = $helper;
        $this->currencyFactory = $currencyFactory;
        $this->mpSaleslist = $mpSaleslist;
        $this->mpOrders = $mpOrders;
        parent::__construct($context);
    }

    /**
     * create amazon order on magento
     * @param  array $orderData
     * @return array
     */
    public function createRakutenOrderAtMage($orderData)
    {
        $productNameError = '';
        try {
            if (!$orderData['shipping_address']['street'] || !$orderData['shipping_address']['country_id']) {
                return ['error' => 1,'msg' => __('order id ').$orderData['rakuten_order_id'].__(' not contain address')];
            }

            $storeId = $this->helper->getDefaultStoreOrderSync();
            $store = $this->storeManager->getStore($storeId);
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($orderData['email']);

            if (!$customer->getEntityId()) {
                $customer->setWebsiteId($websiteId)
                        ->setStore($store)
                        ->setFirstname($orderData['shipping_address']['firstname'])
                        ->setLastname($orderData['shipping_address']['lastname'])
                        ->setEmail($orderData['email'])
                        ->setPassword($orderData['email']);
                $customer->save();
            }

            $cartId = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cartId);

            $quote->setStore($store);

            $amazonCurrency =  $this->currencyFactory->create()->load($orderData['currency_id']);

            $this->storeManager->getStore($storeId)->setCurrentCurrency($amazonCurrency);

            $customer = $this->customerRepository->getById($customer->getEntityId());
            
            $quote->setCurrency();
            $quote->assignCustomer($customer);
            if (!empty($orderData['items'])) {
                foreach ($orderData['items'] as $item) {
                    $product = $this->productFactory->create()->load($item['product_id']);
                    $productNameError = $productNameError .' '. $product->getName().'( SKU : '.$product->getSku().')';
                    $product->setPrice($item['price']);
                    $quote->addProduct(
                        $product,
                        (int)$item['qty']
                    );
                }
            } else {
                $result = [
                    'error' => 1,
                    'msg' => __('order id ').$orderData['rktn_order_id'].__(' not created on your store')
                ];
                return $result;
            }
            
            //Set Address to quote
            $quote->getBillingAddress()->addData($orderData['shipping_address']);
            $quote->getShippingAddress()->addData($orderData['shipping_address']);

            // Collect Rates and Set Shipping & Payment Method
            $shipmethod = 'mis_rakutenshipment_mis_rakutenshipment';
            // Collect Rates and Set Shipping & Payment Method
            $this->shippingRate
                ->setCode('mis_rakutenshipment_mis_rakutenshipment')
                ->getPrice(1);

            //store shipping data in session
            $this->backendSession->setAmzShipDetail($orderData['shipping_service']);
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod('mis_rakutenshipment_mis_rakutenshipment');
            $quote->getShippingAddress()->addShippingRate($this->shippingRate);

            $quote->setPaymentMethod('mis_rktnpayment');
            $quote->setInventoryProcessed(false);

            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => 'mis_rktnpayment']);

            $quote->save();
            // Collect Totals & Save Quote
            $quote->collectTotals();
            // Create Order From Quote
            $quote = $this->cartRepositoryInterface->get($quote->getId());
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
            $order = $this->order->load($orderId);

            $orderStatus = $this->helper->getOrderStatus();
            $order->setStatus($orderStatus)->setCreatedAt($orderData['purchase_date'])->save();
            $order->setEmailSent(0);
            $incrementId = $order->getRealOrderId();
            // Resource Clean-Up
            $quote = $customer = $service = null;
            if ($order->getEntityId()) {
                $result['order_id'] = $order->getRealOrderId();
            } else {
                $result = [
                    'error' => 1,
                    'msg' => __('order id ').$orderData['rktn_order_id'].__(' not created on your store')
                ];
            }
            // set order data in marketplace table
            $this->createMarketplaceOrder($result['order_id']);
            
            return $result;
        } catch (\Exception $e) {
            $errorMsg = empty($productNameError) ? $e->getMessage() : $productNameError. ' is out of stock. please increase the stock to create order.';
            $result = [
                'error' => 1,
                'msg' => $errorMsg,
                'product_ids' => json_encode($orderData['items'])
            ];
            return $result;
        }
    }

    /**
     * create marketplace order
     * @param int $orderId
     * @return void
     */
    private function createMarketplaceOrder($orderId)
    {
        try {
            $saleslistData = [];
            $ordersData = [];
            $collectionSaleslist = $this->mpSaleslist
                                ->create()
                                ->getCollection()
                                ->addFieldToFilter('order_id', $orderId);
            foreach ($collectionSaleslist as $rowSaleslist) {
                $rowSaleslist->setTotalTax(0);
                $rowSaleslist->setTotalCommission(0);
                $rowSaleslist->setActualSellerAmount($rowSaleslist->getTotalAmount());
                $rowSaleslist->setCommissionRate(0);
                $rowSaleslist->setIsAmazon(1);
                $rowSaleslist->save();
            }

            $collectionOrders = $this->mpOrders->create()
                            ->getCollection()
                            ->addFieldToFilter('order_id', $orderId);
            foreach ($collectionOrders as $rowOrders) {
                $rowOrders->setTaxToSeller(0);
                $rowOrders->setTotalTax(0);
                $rowOrders->setIsAmazon(1);
                $rowOrders->save();
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper Order :: error -: '.$e->getMessage());
        }
    }
}
