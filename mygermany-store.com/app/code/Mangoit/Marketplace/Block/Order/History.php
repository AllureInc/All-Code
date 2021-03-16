<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Marketplace\Block\Order;

/*
 * Webkul Marketplace Order History Block
 */
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class History extends \Webkul\Marketplace\Block\Order\History
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    public $customer;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $order;

    /**
     * @var Session
     */
    public $_customerSession;

    /**
     * @var CollectionFactory
     */
    public $_orderCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    public $salesOrderLists;

    /** @var \Magento\Sales\Model\OrderRepository */
    public $orderRepository;

    /** @var \Magento\Catalog\Model\ProductRepository */
    public $productRepository;

    /** @var SaleslistFactory */
    public $saleslistModel;

    /** @var Webkul\Marketplace\Helper\Orders */
    public $ordersHelper;
    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var MpHelper
     */
    protected $logger;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OrderFactory                                     $order
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param CollectionFactory                                $orderCollectionFactory
     * @param \Magento\Sales\Model\OrderRepository             $orderRepository
     * @param \Magento\Catalog\Model\ProductRepository         $productRepository
     * @param SaleslistFactory                                 $saleslistModel
     * @param \Webkul\Marketplace\Helper\Orders                $ordersHelper
     * @param MpHelper                                         $mpHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        OrderFactory $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        SaleslistFactory $saleslistModel,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        MpHelper $mpHelper,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->saleslistModel = $saleslistModel;
        $this->ordersHelper = $ordersHelper;
        $this->mpHelper = $mpHelper;
        $this->logger = $this->getLogger();
        parent::__construct($context, $data, $order, $customer, $customerSession, $orderCollectionFactory, $orderRepository, $productRepository, $saleslistModel, $ordersHelper, $mpHelper);
    }


    public function getPricebyorder($orderId)
    {
        $this->logger->info(' -- getPricebyorder --');
        $this->logger->info(' -- Order ID: '.$orderId.' --');
        $sellerId = $this->getCustomerId();
        $collection = $this->saleslistModel->create()
                      ->getCollection()
                      ->addFieldToFilter(
                          'main_table.seller_id',
                          $sellerId
                      )->addFieldToFilter(
                          'main_table.order_id',
                          $orderId
                      )->getPricebyorderData();
        $name = '';
        $actualSellerAmount = 0;
        foreach ($collection as $coll) {
            // calculate order actual_seller_amount in base currency
            $appliedCouponAmount = $coll['applied_coupon_amount']*1;
            $this->logger->info(' -- appliedCouponAmount: '.$appliedCouponAmount.' --');

            $shippingAmount = $coll['shipping_charges']*1;
            $this->logger->info(' -- shippingAmount: '.$shippingAmount.' --');

            $refundedShippingAmount = $coll['refunded_shipping_charges']*1;
            $this->logger->info(' -- refundedShippingAmount: '.$refundedShippingAmount.' --');

            $totalshipping = $shippingAmount - $refundedShippingAmount;
            $this->logger->info(' -- totalshipping: '.$totalshipping.' --');

            if ($coll['tax_to_seller']) {
                $vendorTaxAmount = $coll['total_tax']*1;
                $this->logger->info(' -- vendorTaxAmount: '.$vendorTaxAmount.' --');
            } else {
                $vendorTaxAmount = 0;
                $this->logger->info(' -- vendorTaxAmount: '.$vendorTaxAmount.' --');
            }

            if ($coll['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $this->logger->info(' -- taxShippingTotal: '.$taxShippingTotal.' --');
                $actualSellerAmount += $coll['actual_seller_amount'] + $taxShippingTotal;
                $this->logger->info(' -- actualSellerAmount: '.$actualSellerAmount.' --');
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                     $this->logger->info(' -- actualSellerAmount: '.$actualSellerAmount.' --');
                }
            }
        }
        $this->logger->info(' ');

        return $actualSellerAmount;
    }

    public function getOrderedPricebyorder($currencyRate, $basePrice)
    {
        return $basePrice * $currencyRate;
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/History.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;      
    }
}
